<?php

declare(strict_types=1);

use Lego\Auth\Authenticator;
use Lego\Auth\Installer;
use Lego\Auth\InvalidCredentials;
use Lego\Auth\Passwords;
use Lego\Auth\Role;
use Lego\Auth\SessionStore;
use Lego\Auth\User;
use Lego\Errors\TooManyRequests;
use Tests\Support\FrozenClock;

function makeUser(string $email = 'ana@example.com', string $password = 'secret-123', array $permissions = ['clients.*'], bool $active = true): User
{
    $role = Role::query()->firstOrCreate(['name' => 'sales'], ['label' => 'Ventas', 'permissions' => $permissions]);

    return User::query()->create([
        'name' => 'Ana',
        'email' => $email,
        'password_hash' => Passwords::hash($password),
        'role_id' => $role->id,
        'is_active' => $active,
    ]);
}

function auditRows(string $action): array
{
    return testDatabase()->connection()->table('action_log')->where('action', $action)->orderBy('id')->get()->all();
}

describe('installer', function () {
    it('creates an admin role with every permission and a generated password', function () {
        $result = (new Installer())->install(' Admin@Example.com ', 'Admin');
        $user = User::findByEmail('admin@example.com');

        expect($result['generated'])->toBeTrue()
            ->and($result['email'])->toBe('admin@example.com')
            ->and(Passwords::verify($result['password'], $user->password_hash))->toBeTrue()
            ->and($user->toIdentity()->can('anything.at_all'))->toBeTrue();
    });

    it('uses a provided password and does nothing once users exist', function () {
        $first = (new Installer())->install('admin@example.com', 'Admin', 'given-password');
        $second = (new Installer())->install('other@example.com', 'Other');

        expect($first['generated'])->toBeFalse()
            ->and(Passwords::verify('given-password', User::findByEmail('admin@example.com')->password_hash))->toBeTrue()
            ->and($second)->toBeNull()
            ->and(User::query()->count())->toBe(1);
    });

    it('rejects an invalid email', function () {
        (new Installer())->install('not-an-email', 'Admin');
    })->throws(InvalidArgumentException::class);
});

describe('sessions', function () {
    it('stores only the hash of the token and resolves the identity', function () {
        $user = makeUser();
        $store = new SessionStore(testDatabase(), new FrozenClock());

        $token = $store->start($user, '10.0.0.1', 'Firefox');
        $identity = $store->find($token);
        $row = testDatabase()->connection()->table('sessions')->first();

        expect($row->id)->toBe(hash('sha256', $token))->not->toBe($token)
            ->and($identity->email)->toBe('ana@example.com')
            ->and($identity->permissions)->toBe(['clients.*'])
            ->and($identity->sessionId)->toBe($row->id);
    });

    it('expires after the idle time and renews with activity', function () {
        $clock = new FrozenClock();
        $store = new SessionStore(testDatabase(), $clock, idleMinutes: 30, absoluteDays: 30);
        $token = $store->start(makeUser());

        $clock->advance('+25 minutes');
        expect($store->find($token))->not->toBeNull();

        $clock->advance('+25 minutes');
        expect($store->find($token))->not->toBeNull();

        $clock->advance('+31 minutes');
        expect($store->find($token))->toBeNull();
    });

    it('expires at the absolute limit even with constant activity', function () {
        $clock = new FrozenClock();
        $store = new SessionStore(testDatabase(), $clock, idleMinutes: 60 * 24, absoluteDays: 2);
        $token = $store->start(makeUser());

        $clock->advance('+23 hours');
        expect($store->find($token))->not->toBeNull();
        $clock->advance('+23 hours');
        expect($store->find($token))->not->toBeNull();
        $clock->advance('+3 hours');
        expect($store->find($token))->toBeNull();
    });

    it('stops working after revocation or deactivation', function () {
        $store = new SessionStore(testDatabase(), new FrozenClock());
        $user = makeUser();
        $revoked = $store->start($user);
        $other = $store->start($user);

        $store->revoke($revoked);
        expect($store->find($revoked))->toBeNull()->and($store->find($other))->not->toBeNull();

        $user->update(['is_active' => false]);
        expect($store->find($other))->toBeNull();
    });

    it('revokes every session of a user', function () {
        $store = new SessionStore(testDatabase(), new FrozenClock());
        $user = makeUser();
        $tokens = [$store->start($user), $store->start($user)];

        expect($store->revokeAllFor($user->id))->toBe(2)
            ->and(array_map($store->find(...), $tokens))->toBe([null, null]);
    });

    it('ignores malformed tokens', function () {
        expect((new SessionStore(testDatabase(), new FrozenClock()))->find("' OR 1=1 --"))->toBeNull();
    });
});

describe('authenticator', function () {
    beforeEach(function () {
        $this->clock = new FrozenClock();
        $this->sessions = new SessionStore(testDatabase(), $this->clock);
        $this->auth = new Authenticator(testDatabase(), $this->sessions, new Lego\Audit\ActionLog(testDatabase(), $this->clock), $this->clock);
    });

    it('signs in with the right password, case-insensitively by email', function () {
        makeUser();

        $result = $this->auth->attempt('ANA@example.com ', 'secret-123', '10.0.0.1', 'Firefox', 'trace1');
        $log = auditRows('auth.login');

        expect($this->sessions->find($result['token'])->email)->toBe('ana@example.com')
            ->and($result['user']->fresh()->last_login_at)->not->toBeNull()
            ->and($log)->toHaveCount(1)
            ->and($log[0]->status)->toBe('ok')
            ->and($log[0]->trace_id)->toBe('trace1')
            ->and(json_decode($log[0]->input, true))->toBe(['email' => 'ana@example.com']);
    });

    it('fails the same way for a wrong password, an unknown email and an inactive user', function () {
        makeUser();
        makeUser('off@example.com', active: false);

        foreach ([['ana@example.com', 'wrong'], ['nobody@example.com', 'secret-123'], ['off@example.com', 'secret-123']] as [$email, $password]) {
            try {
                $this->auth->attempt($email, $password);
                $this->fail("Expected InvalidCredentials for {$email}");
            } catch (InvalidCredentials $e) {
                expect($e->getMessage())->toBe('Correo o contraseña incorrectos.');
            }
        }

        expect(array_column(auditRows('auth.login'), 'status'))->toBe(['denied', 'denied', 'denied'])
            ->and(testDatabase()->connection()->table('sessions')->count())->toBe(0);
    });

    it('never stores the password in the audit log', function () {
        makeUser();
        try {
            $this->auth->attempt('ana@example.com', 'wrong-password-value');
        } catch (InvalidCredentials) {
        }

        expect(json_encode(auditRows('auth.login')))->not->toContain('wrong-password-value');
    });

    it('throttles an email after too many failures and recovers after the window', function () {
        makeUser();
        for ($i = 0; $i < Authenticator::MAX_FAILURES; $i++) {
            try {
                $this->auth->attempt('ana@example.com', 'wrong');
            } catch (InvalidCredentials) {
            }
        }

        expect(fn () => $this->auth->attempt('ana@example.com', 'secret-123'))->toThrow(TooManyRequests::class);

        $this->clock->advance('+' . (Authenticator::WINDOW_MINUTES + 1) . ' minutes');
        expect($this->auth->attempt('ana@example.com', 'secret-123')['token'])->toMatch('/^[0-9a-f]{64}$/');
    });

    it('resets the failure count after a successful login', function () {
        makeUser();
        for ($i = 0; $i < Authenticator::MAX_FAILURES - 1; $i++) {
            try {
                $this->auth->attempt('ana@example.com', 'wrong');
            } catch (InvalidCredentials) {
            }
        }
        $this->clock->advance('+1 second');
        $this->auth->attempt('ana@example.com', 'secret-123');
        $this->clock->advance('+1 second');

        try {
            $this->auth->attempt('ana@example.com', 'wrong');
        } catch (InvalidCredentials) {
        }

        expect($this->auth->attempt('ana@example.com', 'secret-123')['token'])->toBeString();
    });
});

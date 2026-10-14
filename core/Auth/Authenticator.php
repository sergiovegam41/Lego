<?php

declare(strict_types=1);

namespace Lego\Auth;

use Lego\Audit\ActionLog;
use Lego\Database\Database;
use Lego\Errors\TooManyRequests;
use Lego\Support\Clock;

/**
 * Login and logout.
 *
 * - Missing user, wrong password and inactive user fail the same way and take
 *   the same time.
 * - After too many failures for one email in a short window, logins for that
 *   email are refused until the window passes or a login succeeds. The limit
 *   is per email, not per IP, because behind a proxy every client shares one.
 * - Every attempt is written to the action log.
 */
final class Authenticator
{
    public const MAX_FAILURES = 10;

    public const WINDOW_MINUTES = 15;

    public function __construct(
        private readonly Database $db,
        private readonly SessionStore $sessions,
        private readonly ActionLog $log,
        private readonly Clock $clock,
    ) {
    }

    /**
     * @return array{token: string, user: User}
     *
     * @throws InvalidCredentials
     * @throws TooManyRequests
     */
    public function attempt(string $email, string $password, ?string $ip = null, ?string $userAgent = null, ?string $traceId = null): array
    {
        $email = User::normalizeEmail($email);
        $context = ['email' => $email];

        if ($this->recentFailures($email) >= self::MAX_FAILURES) {
            $this->log->record('auth.login', ActionLog::VIA_UI, ActionLog::DENIED, null, 'throttled', $context, $traceId, $ip);
            throw new TooManyRequests();
        }

        $user = $email === '' ? null : User::query()->with('role')->where('email', $email)->first();
        $valid = $user !== null && Passwords::verify($password, $user->password_hash);
        if ($user === null) {
            Passwords::verifyDummy($password);
        }

        if (!$valid || !$user->is_active) {
            $this->log->record('auth.login', ActionLog::VIA_UI, ActionLog::DENIED, $user?->id, null, $context, $traceId, $ip);
            throw new InvalidCredentials();
        }

        if (Passwords::needsRehash($user->password_hash)) {
            $user->password_hash = Passwords::hash($password);
        }
        $user->last_login_at = $this->clock->now();
        $user->save();

        $token = $this->sessions->start($user, $ip, $userAgent);
        $this->log->record('auth.login', ActionLog::VIA_UI, ActionLog::OK, $user->id, null, $context, $traceId, $ip);

        return ['token' => $token, 'user' => $user];
    }

    public function logout(Identity $user, string $token, ?string $ip = null, ?string $traceId = null): void
    {
        $this->sessions->revoke($token);
        $this->log->record('auth.logout', ActionLog::VIA_UI, ActionLog::OK, $user->id, null, null, $traceId, $ip);
    }

    /** Failed logins for an email inside the window and after its last success. */
    private function recentFailures(string $email): int
    {
        $since = $this->clock->now()->modify('-' . self::WINDOW_MINUTES . ' minutes')->format(DATE_ATOM);
        $logins = $this->db->connection()->table('action_log')
            ->where('action', 'auth.login')
            ->whereRaw("input->>'email' = ?", [$email]);

        $lastSuccess = (clone $logins)->where('status', ActionLog::OK)->max('occurred_at');
        $failures = (clone $logins)->where('status', ActionLog::DENIED)->where('occurred_at', '>', $since);
        if ($lastSuccess !== null) {
            $failures->where('occurred_at', '>', $lastSuccess);
        }

        return $failures->count();
    }
}

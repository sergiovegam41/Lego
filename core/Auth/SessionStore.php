<?php

declare(strict_types=1);

namespace Lego\Auth;

use DateTimeImmutable;
use Lego\Database\Database;
use Lego\Support\Clock;

/**
 * Server-side sessions in PostgreSQL (decision D3).
 *
 * The browser holds a random 256-bit token; the database stores only its
 * SHA-256. A session is valid until the first of: idle expiry (renewed on
 * activity), absolute expiry, revocation, or the user being deactivated.
 */
final class SessionStore
{
    /** Activity closer than this does not renew the session, to avoid a write per request. */
    private const TOUCH_INTERVAL_SECONDS = 60;

    public function __construct(
        private readonly Database $db,
        private readonly Clock $clock,
        public readonly int $idleMinutes = 480,
        public readonly int $absoluteDays = 30,
    ) {
    }

    /** Creates a session and returns the token for the cookie. */
    public function start(User $user, ?string $ip = null, ?string $userAgent = null): string
    {
        $token = bin2hex(random_bytes(32));
        $now = $this->clock->now();

        $this->db->connection()->table('sessions')->insert([
            'id' => self::hashToken($token),
            'user_id' => $user->id,
            'ip' => $ip,
            'user_agent' => $userAgent === null ? null : mb_substr($userAgent, 0, 255),
            'created_at' => self::format($now),
            'last_seen_at' => self::format($now),
            'expires_at' => self::format($now->modify("+{$this->idleMinutes} minutes")),
            'absolute_expires_at' => self::format($now->modify("+{$this->absoluteDays} days")),
        ]);

        return $token;
    }

    /** Returns the identity for a valid token and renews its idle expiry. */
    public function find(string $token): ?Identity
    {
        if (preg_match('/^[0-9a-f]{64}$/', $token) !== 1) {
            return null;
        }

        $id = self::hashToken($token);
        $now = $this->clock->now();
        $session = $this->db->connection()->table('sessions')
            ->where('id', $id)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', self::format($now))
            ->where('absolute_expires_at', '>', self::format($now))
            ->first();

        if ($session === null) {
            return null;
        }

        $user = User::query()->with('role')->whereKey($session->user_id)->where('is_active', true)->first();
        if ($user === null) {
            return null;
        }

        $this->touch($id, $now, new DateTimeImmutable((string) $session->last_seen_at), new DateTimeImmutable((string) $session->absolute_expires_at));

        return $user->toIdentity($id);
    }

    public function revoke(string $token): void
    {
        $this->db->connection()->table('sessions')
            ->where('id', self::hashToken($token))
            ->whereNull('revoked_at')
            ->update(['revoked_at' => self::format($this->clock->now())]);
    }

    public function revokeAllFor(int $userId): int
    {
        return $this->db->connection()->table('sessions')
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => self::format($this->clock->now())]);
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    private function touch(string $id, DateTimeImmutable $now, DateTimeImmutable $lastSeen, DateTimeImmutable $absolute): void
    {
        if ($now->getTimestamp() - $lastSeen->getTimestamp() < self::TOUCH_INTERVAL_SECONDS) {
            return;
        }

        $idle = $now->modify("+{$this->idleMinutes} minutes");
        $this->db->connection()->table('sessions')->where('id', $id)->update([
            'last_seen_at' => self::format($now),
            'expires_at' => self::format($idle < $absolute ? $idle : $absolute),
        ]);
    }

    private static function format(DateTimeImmutable $time): string
    {
        return $time->format('Y-m-d H:i:sP');
    }
}

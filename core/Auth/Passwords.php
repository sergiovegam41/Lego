<?php

declare(strict_types=1);

namespace Lego\Auth;

/** Password hashing with PHP's native API; Argon2id when available. */
final class Passwords
{
    private static ?string $dummyHash = null;

    public static function hash(string $password): string
    {
        return password_hash($password, self::algorithm());
    }

    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, self::algorithm());
    }

    /**
     * Burns the same time as a real verification, so a missing user is not
     * distinguishable from a wrong password by response time.
     */
    public static function verifyDummy(string $password): void
    {
        self::$dummyHash ??= self::hash(bin2hex(random_bytes(16)));
        password_verify($password, self::$dummyHash);
    }

    /** Random password for generated accounts, printed once by the CLI. */
    public static function generate(int $length = 20): string
    {
        $alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $password;
    }

    private static function algorithm(): string
    {
        return defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
    }
}

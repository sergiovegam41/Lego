<?php

declare(strict_types=1);

namespace Lego\Auth;

use InvalidArgumentException;

/**
 * First-run setup: an "admin" role with every permission and one active
 * administrator. Does nothing when any user exists, so it is safe to run on
 * every deploy.
 */
final class Installer
{
    public const ADMIN_ROLE = 'admin';

    /**
     * @return array{email: string, password: string, generated: bool}|null Null when already installed.
     */
    public function install(string $email, string $name, ?string $password = null): ?array
    {
        if (User::query()->exists()) {
            return null;
        }

        $email = User::normalizeEmail($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException("Invalid administrator email: {$email}");
        }

        $generated = $password === null || $password === '';
        $password = $generated ? Passwords::generate() : $password;

        $role = Role::query()->firstOrCreate(
            ['name' => self::ADMIN_ROLE],
            ['label' => 'Administrador', 'permissions' => ['*']],
        );

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password_hash' => Passwords::hash($password),
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        return ['email' => $email, 'password' => $password, 'generated' => $generated];
    }
}

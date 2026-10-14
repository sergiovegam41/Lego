<?php

declare(strict_types=1);

namespace Lego\Modules\Users;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lego\Action\Action;
use Lego\Auth\Identity;
use Lego\Auth\Passwords;
use Lego\Data\Fields\Boolean;
use Lego\Data\Fields\Email;
use Lego\Data\Fields\Select;
use Lego\Data\Fields\Text;
use Lego\Data\Record;
use Lego\Errors\BusinessRuleViolation;
use Lego\Module\Module;
use Lego\Modules\Roles\Role;

/**
 * Users of the application: a core module built with the same tools as any
 * application module.
 *
 * Passwords are never typed into a form. A new user has no usable password
 * until an administrator runs "Restablecer contraseña", which shows a
 * generated one once and closes the user's sessions.
 *
 * @property int    $id
 * @property string $name
 * @property string $email
 * @property string $password_hash
 * @property int    $role_id
 * @property bool   $is_active
 * @property Role   $role
 */
#[Module(label: 'Usuarios', icon: 'person-outline', order: 900)]
final class User extends Record
{
    protected $table = 'users';

    protected $hidden = ['password_hash'];

    protected $casts = ['is_active' => 'boolean', 'last_login_at' => 'datetime'];

    public static function fields(): array
    {
        return [
            Text::make('name', 'Nombre')->required()->max(120)->searchable()->sortable(),
            Email::make('email', 'Correo')->required()->unique()->searchable()->sortable(),
            Select::make('role_id', 'Rol')->required()
                ->optionsFrom(static fn (): array => Role::query()->orderBy('label')->pluck('label', 'id')->all()),
            Boolean::make('is_active', 'Activo')->default(true),
        ];
    }

    /** @return BelongsTo<Role, $this> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function beforeChange(string $operation, array $clean, Identity $by): void
    {
        if ($this->getKey() !== $by->id) {
            return;
        }

        if ($operation === 'delete') {
            throw new BusinessRuleViolation('cannot_delete_self', 'No puedes eliminar tu propio usuario.');
        }
        if ($operation === 'update' && array_key_exists('is_active', $clean) && $clean['is_active'] === false) {
            throw new BusinessRuleViolation('cannot_deactivate_self', 'No puedes desactivar tu propio usuario.');
        }
        if ($operation === 'update' && isset($clean['role_id']) && (int) $clean['role_id'] !== $this->role_id) {
            throw new BusinessRuleViolation('cannot_change_own_role', 'No puedes cambiar tu propio rol.');
        }
    }

    #[Action(
        label: 'Restablecer contraseña',
        description: 'Genera una contraseña nueva, la muestra una sola vez y cierra todas las sesiones del usuario.',
        confirm: true,
    )]
    public function resetPassword(): array
    {
        $password = Passwords::generate();
        $this->forceFill(['password_hash' => Passwords::hash($password)])->save();
        $this->getConnection()->table('sessions')
            ->where('user_id', $this->getKey())
            ->whereNull('revoked_at')
            ->update(['revoked_at' => date(DATE_ATOM)]);

        return [
            'password' => $password,
            'message' => "Contraseña nueva para {$this->email}: {$password}. Cópiala ahora; no se volverá a mostrar.",
        ];
    }

    /** Emails are always stored lowercase and trimmed. */
    public function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = self::normalizeEmail($value);
    }

    public static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    public static function findByEmail(string $email): ?self
    {
        return self::query()->where('email', self::normalizeEmail($email))->first();
    }

    public function toIdentity(?string $sessionId = null): Identity
    {
        return new Identity(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            role: $this->role->name,
            permissions: array_values($this->role->permissions ?? []),
            sessionId: $sessionId,
        );
    }
}

<?php

declare(strict_types=1);

namespace Lego\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int    $id
 * @property string $name
 * @property string $email
 * @property string $password_hash
 * @property int    $role_id
 * @property bool   $is_active
 * @property Role   $role
 */
final class User extends Model
{
    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password_hash', 'role_id', 'is_active'];

    protected $hidden = ['password_hash'];

    protected $casts = ['is_active' => 'boolean', 'last_login_at' => 'datetime'];

    /** @return BelongsTo<Role, $this> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
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

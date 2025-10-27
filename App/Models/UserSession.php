<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class UserSession extends Model
{
    protected $table = 'auth_user_sessions';
    public $timestamps = true;

    protected $fillable = [
        'auth_user_id',
        'device_id',
        'refresh_token',
        'access_token',
        'firebase_token',
        'expires_at',
        'refresh_expires_at',
        'is_active',
        'last_activity_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'refresh_expires_at' => 'datetime',
        'is_active' => 'boolean',
        'last_activity_at' => 'datetime'
    ];

    /**
     * Verifica si la sesión sigue siendo válida
     */
    public function isValid()
    {
        return $this->is_active && $this->expires_at->gt(Carbon::now());
    }

    /**
     * Verifica si el refresh_token sigue siendo válido
     */
    public function canRefresh()
    {
        return $this->is_active && $this->refresh_expires_at->gt(Carbon::now());
    }

    /**
     * Invalida la sesión actual
     */
    public function invalidate()
    {
        $this->update(['is_active' => false]);
    }
}

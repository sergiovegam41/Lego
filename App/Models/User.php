<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Core\Attributes\ApiGetResource;

/**
 * User Model
 *
 * FILOSOFÍA LEGO:
 * Modelo para gestión de usuarios del sistema.
 */
#[ApiGetResource(
    endpoint: 'users-config',
    pagination: 'offset',
    perPage: 20,
    sortable: ['id', 'name', 'email', 'auth_group_id', 'role_id', 'status', 'created_at'],
    filterable: ['id', 'name', 'email', 'auth_group_id', 'role_id', 'status'],
    searchable: ['name', 'email']
)]
class User extends Model
{
    protected $table = 'auth_users';
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'auth_group_id',
        'role_id',
        'status'
    ];
    
    protected $hidden = [
        'password'
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}

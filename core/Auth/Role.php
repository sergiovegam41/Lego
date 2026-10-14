<?php

declare(strict_types=1);

namespace Lego\Auth;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int          $id
 * @property string       $name
 * @property string       $label
 * @property list<string> $permissions
 */
final class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = ['name', 'label', 'permissions'];

    protected $casts = ['permissions' => 'array'];
}

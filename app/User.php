<?php

namespace App;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'update_at';

    protected $fillable = [
        'username', 'password', 'signature',
        'Emp_ID', 'Name', 'email', 'Location', 'Position', 'Priv_ID', 'Status', 'Sub_Menu',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}

<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasTranslations;


class User extends Authenticatable
{
    use HasTranslations;
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
    'name',
    'email',
    'password',
    'role_id',
    'mobile',
    'mysql_id',
    'c_active',
    'is_active',
    'is_hold',
    'email_verified',
    'email_verified_at',
];

protected $casts = [
    'role_id'            => \App\Casts\AsObjectId::class,
    'c_active'           => 'boolean',
    'is_active'          => 'boolean',
    'is_hold'            => 'boolean',
    'email_verified'     => 'boolean',
    'email_verified_at'  => 'datetime',
    'password'           => 'hashed',
];
public array $translatable = [
    'name',
];  

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', '_id');
    }

    public function isAdmin(): bool
    {
        // dd($this->role->slug);
        return $this->role && in_array($this->role->slug, ['admin', 'super-admin']);
    }

    // public function isAdmin(): bool
    // {
    //     return in_array($this->role, ['admin', 'super_admin']);
    // }
}
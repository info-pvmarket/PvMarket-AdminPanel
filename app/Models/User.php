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
    'assigned_admin_id',
];

protected $casts = [
    'role_id'            => \App\Casts\AsObjectId::class,
    'assigned_admin_id'  => \App\Casts\AsObjectId::class,
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
        if (empty($this->role_id)) {
            return false;
        }
        $role = $this->role;
        return $role !== null;
    }

    public function isSuperAdmin(): bool
    {
        if (empty($this->role_id)) {
            return false;
        }
        $role = $this->role;
        return $role && $role->slug === 'super-admin';
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_admin_id', '_id');
    }

    public function getAssignedUserIds(): array
    {
        if (empty($this->_id)) {
            return [];
        }

        $users = User::where('assigned_admin_id', new \MongoDB\BSON\ObjectId((string) $this->_id))->get(['_id']);
        return $users->map(fn($user) => (string) $user->_id)->filter(fn($id) => !empty($id))->toArray();
    }
}
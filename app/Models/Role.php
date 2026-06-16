<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use App\Traits\HasTranslations;

class Role extends Model
{
    use HasTranslations;
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'roles';

    protected $fillable = [
        'role',
        'slug',
        'guard_name',
        'access_types',
    ];

    protected $casts = [
        'access_types' => 'array',
    ];
    public array $translatable = [
        'role',
    ];

}
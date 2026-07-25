<?php
namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;

class Incoterm extends Model {
    use HasTranslations;

    protected $connection = 'mongodb';
    protected $collection = 'incoterms';
    protected $fillable = [
    'code',
    'name',
    'is_active',
    'created_by',
    'updated_by',
];
protected $casts = [
    'is_active' => 'boolean',
];

public array $translatable = ['name'];
}

<?php
namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

class Incoterm extends Model {
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
}
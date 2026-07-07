<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Country extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'countries';

    protected $fillable = [
        'name',
        'code',
        'iso2',
        'iso3',
        'capital',
        'currency',
        'flag',
        'alt_tag',
        'is_default',
        'country_files',
    ];

    protected $casts = [
        'is_default'    => 'boolean',
        'country_files' => 'array',
    ];

    public function getTable(): string
    {
        return $this->collection ?? parent::getTable();
    }

    public function getFlagUrlAttribute(): ?string
    {
        if (!$this->flag) return null;

        if (preg_match('/^https?:\/\//i', $this->flag)) {
            return $this->flag;
        }

        $baseUrl = rtrim(config('filesystems.disks.r2.url'), '/');
        return $baseUrl . '/' . ltrim($this->flag, '/');
    }
}

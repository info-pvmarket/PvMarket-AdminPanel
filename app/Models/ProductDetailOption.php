<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;

class ProductDetailOption extends Model
{
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'specifications';

    public function getTable(): string  
    {
        return 'specifications';
    }

    protected $fillable = [
        'name',
        'category_id',
        'category_name',
        'sub_category_id',
        'sub_category_name',
        'unit_ids',
        'unit_names',
        'is_tag',
        'is_active',
    ];

    public function getUnitIdsAttribute(mixed $value): array
    {
        return $this->normalizeArrayAttribute($value);
    }

    public function setUnitIdsAttribute(mixed $value): void
    {
        $this->attributes['unit_ids'] = $this->normalizeArrayAttribute($value);
    }

    public function getUnitNamesAttribute(mixed $value): array
    {
        return $this->normalizeArrayAttribute($value);
    }

    public function setUnitNamesAttribute(mixed $value): void
    {
        $this->attributes['unit_names'] = $this->normalizeArrayAttribute($value);
    }

    private function normalizeArrayAttribute(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_object($value) && method_exists($value, 'getArrayCopy')) {
            $value = $value->getArrayCopy();
        } elseif ($value instanceof \Traversable) {
            $value = iterator_to_array($value);
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            return [$value];
        }

        return is_array($value) ? $value : [$value];
    }

    public array $translatable = [
        'name',
        'category_name',
        'sub_category_name',
        'unit_names',
    ];
}

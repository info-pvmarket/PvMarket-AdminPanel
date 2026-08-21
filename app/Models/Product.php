<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Casts\AsArrayObject;
use App\Traits\HasTranslations;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'products';

    protected $fillable = [
        'sku_code',
        'product_name',
        'product_description',
        'specific_value',
        'specific_value_unit_id',
        'specific_value_unit_name',
        'specific_value_unit_code',
        'brand_id',
        'brand_name',
        'category_id',
        'category_name',
        'sub_category_id',
        'sub_category_name',
        'pieces_per_pallet',
        'pallets_per_container',
        'is_popular',
        'real_time_price',
        'product_details',
        'measurement_details',
        'datasheet',
        'verification_status',
        'is_active',
        'updated_by',
        'created_by',
    ];

    protected $casts = [
        'is_popular'      => 'boolean',
        'real_time_price' => 'boolean',
        'is_active'       => 'boolean',
    ];
public array $translatable = [
    'product_name',
    'product_description',
    'category_name',
    'sub_category_name',
    'brand_name',
    'product_details',
];

    // ── Relationships ───────────────────────────────────────────────

    public function listings()
    {
        return $this->hasMany(ProductListing::class, 'product_id', '_id');
    }

    public function getDatasheetDisplayNameAttribute(): ?string
    {
        $name = data_get($this->datasheet, 'original_name')
            ?: data_get($this->datasheet, 'filename');

        return is_string($name) && trim($name) !== '' ? trim($name) : null;
    }

    public function getDatasheetDisplayUrlAttribute(): ?string
    {
        $url = data_get($this->datasheet, 'url');
        if (is_string($url) && trim($url) !== '') {
            return trim($url);
        }

        $path = data_get($this->datasheet, 'path');

        return is_string($path) && trim($path) !== ''
            ? Storage::disk('public')->url(trim($path))
            : null;
    }
}

<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;
use App\Traits\HasMongoRelationKey;
use App\Casts\AsObjectId;
use MongoDB\BSON\ObjectId;

class SeoMetaData extends Model
{
    use HasTranslations, HasMongoRelationKey;

    protected $connection = 'mongodb';
    protected $collection = 'seo_meta_data';

    protected $fillable = [
        'market_id',
        'category_id',
        'sub_category_id',
        'brand_id',
        'product_id',
        'market_code',
        'page_key',
        'category_slug',
        'sub_category_slug',
        'brand_slug',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'content',
        'page_header',
        'short_description',
        'bottom_header',
        'bottom_description',
        'canonical_url',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'market_id'       => AsObjectId::class,
        'category_id'     => AsObjectId::class,
        'sub_category_id' => AsObjectId::class,
        'brand_id'        => AsObjectId::class,
        'product_id'      => AsObjectId::class,
        'is_active'       => 'boolean',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    public array $translatable = [
        'meta_title',
        'meta_description',
        'meta_keywords',
        'content',
        'page_header',
        'short_description',
        'bottom_header',
        'bottom_description',
    ];

    // Relationships
    public function market()
    {
        return $this->belongsTo(Market::class, 'market_id');
    }

    public function category()
    {
        return $this->belongsTo(MainMenu::class, 'category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubMenu::class, 'sub_category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function ogMeta()
    {
        return $this->hasOne(SeoOGMetaData::class, 'seo_meta_id', 'mongo_relation_id');
    }

    public function twitterMeta()
    {
        return $this->hasOne(SeoTwitterMetaData::class, 'seo_meta_id', 'mongo_relation_id');
    }

    public function robotMeta()
    {
        return $this->hasOne(SeoRobotMetaData::class, 'seo_meta_id', 'mongo_relation_id');
    }

    // Build unique key for this combination
    public function getUniqueKeyAttribute(): string
    {
        return implode('|', [
            $this->market_id ?? 'null',
            $this->category_id ?? 'null',
            $this->sub_category_id ?? 'null',
            $this->brand_id ?? 'null',
            $this->product_id ?? 'null',
        ]);
    }

    // Scope for finding by entity combination
    public function scopeForEntity($query, $marketId = null, $categoryId = null, $subCategoryId = null, $brandId = null, $productId = null)
    {
        foreach ([
            'market_id'       => $marketId,
            'category_id'     => $categoryId,
            'sub_category_id' => $subCategoryId,
            'brand_id'        => $brandId,
            'product_id'      => $productId,
        ] as $field => $value) {
            if ($value === null || $value === '') {
                $query->whereNull($field);
                continue;
            }

            // Older records may contain string IDs while newer records use
            // BSON ObjectIds. Match both representations so API lookups and
            // duplicate checks consistently resolve the intended entity.
            $candidates = [$value];
            if (is_string($value) && preg_match('/^[a-f0-9]{24}$/i', $value)) {
                $candidates[] = new ObjectId($value);
            }

            $query->whereIn($field, $candidates);
        }

        return $query;
    }

    // Get the page type based on entity combination
    public function getPageTypeAttribute(): string
    {
        if ($this->product_id) return 'Product';
        if ($this->brand_id) return 'Brand';
        if ($this->sub_category_id) return 'Sub Category';
        if ($this->category_id) return 'Category';
        if ($this->market_id) return 'Market Home';
        return 'Global Home';
    }
}

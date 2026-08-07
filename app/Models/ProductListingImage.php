<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Casts\AsObjectId;

class ProductListingImage extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'product_listing_images';

    protected $fillable = [
        'product_listing_id',
        'image',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'product_listing_id' => AsObjectId::class,
        'created_by'         => AsObjectId::class,
        'sort_order'         => 'integer',
    ];

    public function getPublicUrlAttribute(): ?string
    {
        return self::resolvePublicUrl(
            $this->image,
            config('filesystems.disks.r2.url')
        );
    }

    public static function resolvePublicUrl(mixed $image, ?string $r2BaseUrl): ?string
    {
        $url = trim((string) data_get($image, 'url', ''));
        $path = trim((string) data_get($image, 'path', ''));
        $value = $url !== '' ? $url : $path;

        if ($value === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        $baseUrl = rtrim(trim((string) $r2BaseUrl), '/');

        return $baseUrl !== ''
            ? $baseUrl.'/'.ltrim($value, '/')
            : null;
    }
}

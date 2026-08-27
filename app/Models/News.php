<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\HasTranslations;
use Illuminate\Support\Facades\Storage;

class News extends Model
{
    use HasTranslations;
    protected $connection = 'mongodb';
    protected $collection = 'news';

    protected $fillable = [
    'title',
    'slug',
    'content',
    'image',
    'alt_tag',
    'description',
    'is_active',
];

protected $casts = [
    'is_active' => 'boolean',
];

public array $translatable = ['title', 'content'];

    public function getImageDisplayUrlAttribute(): ?string
    {
        return self::resolvePublicImageUrl(
            $this->image,
            Storage::disk('public')->url('')
        );
    }

    public static function resolvePublicImageUrl(mixed $image, string $publicStorageUrl): ?string
    {
        $url = data_get($image, 'url')
            ?: data_get($image, 'path')
            ?: (is_string($image) ? $image : null);

        if (! is_string($url) || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        if (preg_match('/^https?:\/\//i', $url) === 1 || str_starts_with($url, '//')) {
            return $url;
        }

        $path = ltrim($url, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return rtrim($publicStorageUrl, '/').'/'.$path;
    }
}

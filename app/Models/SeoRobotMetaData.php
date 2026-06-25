<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Casts\AsObjectId;

class SeoRobotMetaData extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'seo_robot_meta_data';

    protected $fillable = [
        'seo_meta_id',
        'index',
        'follow',
        'noarchive',
        'nosnippet',
        'noimageindex',
        'nocache',
        'max_snippet',
        'max_image_preview',
        'max_video_preview',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'seo_meta_id'       => AsObjectId::class,
        'index'             => 'boolean',
        'follow'            => 'boolean',
        'noarchive'         => 'boolean',
        'nosnippet'         => 'boolean',
        'noimageindex'      => 'boolean',
        'nocache'           => 'boolean',
        'max_snippet'       => 'integer',
        'max_video_preview' => 'integer',
        'is_active'         => 'boolean',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    public function seoMeta()
    {
        return $this->belongsTo(SeoMetaData::class, 'seo_meta_id');
    }

    // Generate robots meta tag content
    public function getRobotsContentAttribute(): string
    {
        $directives = [];

        if ($this->index === false) {
            $directives[] = 'noindex';
        } else {
            $directives[] = 'index';
        }

        if ($this->follow === false) {
            $directives[] = 'nofollow';
        } else {
            $directives[] = 'follow';
        }

        if ($this->noarchive) $directives[] = 'noarchive';
        if ($this->nosnippet) $directives[] = 'nosnippet';
        if ($this->noimageindex) $directives[] = 'noimageindex';
        if ($this->nocache) $directives[] = 'nocache';

        if ($this->max_snippet !== null) {
            $directives[] = "max-snippet:{$this->max_snippet}";
        }
        if ($this->max_image_preview) {
            $directives[] = "max-image-preview:{$this->max_image_preview}";
        }
        if ($this->max_video_preview !== null) {
            $directives[] = "max-video-preview:{$this->max_video_preview}";
        }

        return implode(', ', $directives);
    }
}

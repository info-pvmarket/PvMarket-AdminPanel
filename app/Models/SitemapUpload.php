<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class SitemapUpload extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sitemap_uploads';
}

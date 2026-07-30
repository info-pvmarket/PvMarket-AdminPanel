<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection('mongodb')
            ->getDatabase()
            ->selectCollection('website_translations')
            ->createIndex(
                ['language' => 1],
                ['name' => 'website_translations_language_unique', 'unique' => true],
            );
    }

    public function down(): void
    {
        DB::connection('mongodb')
            ->getDatabase()
            ->selectCollection('website_translations')
            ->dropIndex('website_translations_language_unique');
    }
};

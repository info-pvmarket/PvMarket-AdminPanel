<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A13: nothing enforced slug uniqueness at the database level, so a delayed
 * Create request followed by a retry could insert two "blogs" documents with
 * the same slug. The application-level check added in BlogController is the
 * friendly guard; this index is the authoritative one that survives
 * concurrent/retried requests racing each other.
 *
 * Use a separate normalized key so existing published URLs are not renamed.
 * Stop before writes if legacy records collide; an owner must resolve them.
 */
return new class extends Migration
{
    public function up(): void
    {
        $blogs = DB::connection('mongodb')->getDatabase()->selectCollection('blogs');
        $keys = [];
        $updates = [];
        foreach ($blogs->find([], ['projection' => ['slug' => 1]]) as $blog) {
            $slug = Str::slug((string) ($blog['slug'] ?? ''));
            if ($slug === '') continue;
            if (isset($keys[$slug])) {
                throw new RuntimeException('A13: duplicate normalized blog slugs exist. Resolve duplicates before rerunning this migration; no records were changed.');
            }
            $keys[$slug] = true;
            $updates[] = ['updateOne' => [['_id' => $blog['_id']], ['$set' => ['slug_normalized' => $slug]]]];
        }
        if ($updates) $blogs->bulkWrite($updates);

        $blogs->createIndex(
                ['slug_normalized' => 1],
                [
                    'name' => 'blogs_slug_normalized_unique',
                    'unique' => true,
                    'partialFilterExpression' => [
                        'slug_normalized' => ['$type' => 'string', '$gt' => ''],
                    ],
                ],
            );
    }

    public function down(): void
    {
        DB::connection('mongodb')
            ->getDatabase()
            ->selectCollection('blogs')
            ->dropIndex('blogs_slug_normalized_unique');
    }
};

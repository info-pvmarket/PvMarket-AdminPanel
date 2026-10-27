<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $blogs = DB::connection('mongodb')->getDatabase()->selectCollection('blogs');
        $owners = [];
        $updates = [];
        foreach ($blogs->find([], ['projection' => ['slug' => 1, 'slug_routes' => 1]]) as $blog) {
            $routes = array_values(array_unique(array_filter(array_map(
                fn ($slug) => Str::slug((string) $slug),
                array_merge((array) ($blog['slug_routes'] ?? []), [$blog['slug'] ?? '']),
            ))));
            foreach ($routes as $route) {
                if ($route === '29' || (isset($owners[$route]) && $owners[$route] !== (string) $blog['_id'])) {
                    throw new RuntimeException('A10: blog URL reservations conflict. Resolve conflicts before rerunning; no records were changed.');
                }
                $owners[$route] = (string) $blog['_id'];
            }
            if ($routes) $updates[] = ['updateOne' => [['_id' => $blog['_id']], ['$set' => ['slug_routes' => $routes]]]];
        }
        if ($updates) $blogs->bulkWrite($updates);
        $blogs->createIndex(['slug_routes' => 1], [
            'name' => 'blogs_slug_routes_unique', 'unique' => true,
            'partialFilterExpression' => ['slug_routes.0' => ['$exists' => true]],
        ]);
    }

    public function down(): void
    {
        DB::connection('mongodb')->getDatabase()->selectCollection('blogs')->dropIndex('blogs_slug_routes_unique');
        // Keep historical addresses; removing an index must not break old links.
    }
};

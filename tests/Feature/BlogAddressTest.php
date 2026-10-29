<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckAdminPermission;
use App\Models\Blog;
use App\Services\TranslationService;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class BlogAddressTest extends TestCase
{
    private ?string $testDatabase = null;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('A10_MONGODB_TESTS') !== '1') $this->markTestSkipped('Opt in to disposable localhost MongoDB tests.');
        $this->testDatabase = 'pvmarket_a10_test_'.bin2hex(random_bytes(8));
        config(['database.connections.mongodb' => ['driver' => 'mongodb', 'dsn' => 'mongodb://127.0.0.1:27017', 'database' => $this->testDatabase], 'session.driver' => 'array', 'languages.available' => ['en' => 'English']]);
        DB::purge('mongodb');
        Storage::fake('public');
        Storage::fake('r2');
        (require database_path('migrations/2026_10_24_000001_add_unique_index_to_blogs_slug.php'))->up();
        $this->migration()->up();
        $this->withoutMiddleware([Authenticate::class, CheckAdminPermission::class]);
        $translator = Mockery::mock(TranslationService::class);
        $translator->shouldNotReceive('translateText');
        $this->app->instance(TranslationService::class, $translator);
        Queue::fake();
    }

    protected function tearDown(): void
    {
        try {
            if ($this->testDatabase && str_starts_with($this->testDatabase, 'pvmarket_a10_test_')) {
                DB::connection('mongodb')->getDatabase()->drop();
                DB::purge('mongodb');
            }
        } finally { parent::tearDown(); }
    }

    private function migration() { return require database_path('migrations/2026_10_25_000003_reserve_blog_slug_routes.php'); }
    private function postBlog(array $data)
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        return $this->post('/admin/knowledge-hub/blogs', array_merge($data, ['image' => UploadedFile::fake()->createWithContent('cover.png', $png)]));
    }
    private function record(): Blog { return Blog::create(['title' => 'Seller guide', 'slug' => 'how-do-you-register-as-a-seller', 'slug_normalized' => 'how-do-you-register-as-a-seller', 'slug_routes' => ['how-do-you-register-as-a-seller'], 'data' => '<p>Saved body</p>', 'is_active' => true, 'is_faq' => false]); }

    public function test_title_edit_with_missing_or_blank_slug_keeps_published_address(): void
    {
        $blog = $this->record();
        foreach ([[], ['slug' => '']] as $extra) {
            $this->put('/admin/knowledge-hub/blogs/'.$blog->getKey(), array_merge(['title' => 'Updated seller title', 'data' => '<p>Saved body</p>'], $extra))->assertSessionHasNoErrors();
            $this->assertSame('how-do-you-register-as-a-seller', $blog->fresh()->slug);
        }
    }

    public function test_explicit_url_changes_retain_all_prior_addresses_and_block_reuse(): void
    {
        $blog = $this->record();
        foreach (['seller-guide-new', 'seller-guide-final'] as $slug) {
            $this->put('/admin/knowledge-hub/blogs/'.$blog->getKey(), ['title' => 'Seller guide', 'slug' => $slug, 'data' => '<p>Saved body</p>'])->assertSessionHasNoErrors();
        }
        $this->assertSame(['how-do-you-register-as-a-seller', 'seller-guide-new', 'seller-guide-final'], $blog->fresh()->slug_routes);
        $this->postBlog(['title' => 'Another guide', 'slug' => 'how-do-you-register-as-a-seller', 'data' => '<p>Other body</p>'])->assertSessionHasErrors('slug');
        $this->assertSame(1, Blog::count());
        $this->expectException(\MongoDB\Driver\Exception\BulkWriteException::class);
        DB::connection('mongodb')->getDatabase()->selectCollection('blogs')->insertOne(['slug' => 'unrelated-new', 'slug_routes' => ['seller-guide-new']]);
    }

    public function test_blank_slug_generation_and_reserved_legacy_address(): void
    {
        $this->postBlog(['title' => 'Valid new title', 'slug' => '', 'data' => '<p>Body</p>'])->assertSessionHasNoErrors();
        $this->assertSame('valid-new-title', Blog::first()->slug);
        $this->postBlog(['title' => '!!!', 'slug' => '', 'data' => '<p>Body</p>'])->assertSessionHasErrors('slug');
        $this->postBlog(['title' => 'Wrong legacy route', 'slug' => '29', 'data' => '<p>Body</p>'])->assertSessionHasErrors('slug');
        $this->assertSame(1, Blog::count());
    }

    public function test_explicit_address_change_updates_its_existing_self_canonical(): void
    {
        $blog = $this->record();
        $blog->update(['seo' => ['canonical_url' => 'https://pv.market/blogs/how-do-you-register-as-a-seller']]);
        $this->put('/admin/knowledge-hub/blogs/'.$blog->getKey(), ['title' => 'Seller guide', 'slug' => 'seller-new', 'data' => '<p>Saved body</p>', 'seo_canonical_url' => 'https://pv.market/blogs/how-do-you-register-as-a-seller'])->assertSessionHasNoErrors();
        $this->assertSame('https://pv.market/blogs/seller-new', $blog->fresh()->seo['canonical_url']);
    }

    public function test_migration_detects_conflicts_before_backfilling_any_record(): void
    {
        $collection = DB::connection('mongodb')->getDatabase()->selectCollection('blogs');
        $collection->dropIndex('blogs_slug_routes_unique');
        $collection->insertMany([['slug' => 'first', 'slug_routes' => ['reserved']], ['slug' => 'reserved']]);
        try { $this->migration()->up(); $this->fail('Expected reservation conflict'); }
        catch (\RuntimeException $exception) { $this->assertStringContainsString('no records were changed', $exception->getMessage()); }
        $this->assertArrayNotHasKey('slug_routes', (array) $collection->findOne(['slug' => 'reserved']));
    }

    public function test_migration_reserves_existing_urls_without_changing_them(): void
    {
        $collection = DB::connection('mongodb')->getDatabase()->selectCollection('blogs');
        $collection->dropIndex('blogs_slug_routes_unique');
        $collection->insertOne(['slug' => 'stable-existing-address', 'title' => 'Existing title']);
        $this->migration()->up();
        $this->migration()->up();
        $record = $collection->findOne(['slug' => 'stable-existing-address']);
        $this->assertSame('Existing title', $record['title']);
        $this->assertSame('stable-existing-address', $record['slug']);
        $this->assertSame(['stable-existing-address'], (array) $record['slug_routes']);
    }
}

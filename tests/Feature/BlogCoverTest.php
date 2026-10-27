<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckAdminPermission;
use App\Models\Blog;
use App\Services\TranslationService;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class BlogCoverTest extends TestCase
{
    private ?string $testDatabase = null;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('A14_MONGODB_TESTS') !== '1') $this->markTestSkipped('Opt in to disposable localhost MongoDB tests.');
        $this->testDatabase = 'pvmarket_a14_test_'.bin2hex(random_bytes(8));
        config(['database.connections.mongodb' => ['driver' => 'mongodb', 'dsn' => 'mongodb://127.0.0.1:27017', 'database' => $this->testDatabase], 'session.driver' => 'array', 'languages.available' => ['en' => 'English']]);
        DB::purge('mongodb');
        Storage::fake('public');
        Queue::fake();
        $translator = Mockery::mock(TranslationService::class);
        $translator->shouldNotReceive('translateText');
        $this->app->instance(TranslationService::class, $translator);
        $this->withoutMiddleware([Authenticate::class, CheckAdminPermission::class]);
    }

    protected function tearDown(): void
    {
        try {
            if ($this->testDatabase && str_starts_with($this->testDatabase, 'pvmarket_a14_test_')) {
                DB::connection('mongodb')->getDatabase()->drop();
                DB::purge('mongodb');
            }
        } finally { parent::tearDown(); }
    }

    private function payload(): array { return ['title' => 'Article-specific guide', 'slug' => 'a14-test-guide', 'data' => '<p>Saved body.</p>']; }
    private function cover(): UploadedFile { return UploadedFile::fake()->createWithContent('guide-cover.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')); }

    public function test_new_blog_requires_cover_before_creating_published_record(): void
    {
        $this->post('/admin/knowledge-hub/blogs', $this->payload())->assertSessionHasErrors('image');
        $this->assertSame(0, Blog::count());
        Queue::assertNothingPushed();
    }

    public function test_valid_cover_is_stored_and_referenced_by_new_article(): void
    {
        $this->post('/admin/knowledge-hub/blogs', $this->payload() + ['image' => $this->cover(), 'alt_tag' => 'Guide cover'])->assertSessionHasNoErrors();
        $blog = Blog::first();
        $this->assertTrue((bool) $blog->is_active);
        $this->assertNotEmpty($blog->image['url']);
        Storage::disk('public')->assertExists($blog->image['path']);
        $this->assertSame('Guide cover', $blog->image['alt']);
    }

    public function test_non_image_upload_cannot_publish_new_blog(): void
    {
        $this->post('/admin/knowledge-hub/blogs', $this->payload() + ['image' => UploadedFile::fake()->createWithContent('cover.txt', 'not an image')])->assertSessionHasErrors('image');
        $this->assertSame(0, Blog::count());
    }

    public function test_existing_articles_remain_editable_and_keep_their_cover(): void
    {
        foreach ([null, ['url' => 'https://assets.pv.market/retained-cover.png', 'path' => 'blogs/retained.png']] as $image) {
            $blog = Blog::create($this->payload() + ['image' => $image, 'is_active' => true, 'is_faq' => false]);
            $this->put('/admin/knowledge-hub/blogs/'.$blog->getKey(), $this->payload())->assertSessionHasNoErrors();
            $this->assertSame($image, $blog->fresh()->image);
            $blog->delete();
        }
    }
}

<?php

namespace Tests\Feature;

use App\Jobs\TranslateBlogJob;
use App\Models\Blog;
use App\Services\TranslationService;
use Illuminate\Auth\Middleware\Authenticate;
use App\Http\Middleware\CheckAdminPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Bus\Dispatcher;
use Mockery;
use MongoDB\Driver\Exception\BulkWriteException;
use RuntimeException;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class BlogSaveTest extends TestCase
{
    private ?string $testDatabase = null;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('A13_MONGODB_TESTS') !== '1') {
            $this->markTestSkipped('Set A13_MONGODB_TESTS=1 to use an isolated localhost MongoDB test database.');
        }
        $this->testDatabase = 'pvmarket_a13_test_'.bin2hex(random_bytes(8));
        config([
            'database.connections.mongodb' => [
                'driver' => 'mongodb', 'dsn' => 'mongodb://127.0.0.1:27017',
                'database' => $this->testDatabase,
            ],
            'languages.available' => ['en' => 'English', 'fr' => 'French', 'de' => 'German'],
            'session.driver' => 'array',
        ]);
        DB::purge('mongodb');
        Storage::fake('public');
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
            if ($this->testDatabase && str_starts_with($this->testDatabase, 'pvmarket_a13_test_')) {
                DB::connection('mongodb')->getDatabase()->drop();
                Blog::flushEventListeners();
                DB::purge('mongodb');
            }
        } finally {
            parent::tearDown();
        }
    }

    private function migration()
    {
        return require database_path('migrations/2026_10_24_000001_add_unique_index_to_blogs_slug.php');
    }

    private function payload(array $overrides = []): array
    {
        return array_replace(['title' => 'A13 test article', 'slug' => 'a13-test', 'data' => '<p>Body</p>'], $overrides);
    }

    private function createPayload(array $overrides = []): array
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        return array_replace($this->payload($overrides), ['image' => UploadedFile::fake()->createWithContent('cover.png', $png)]);
    }

    private function record(array $overrides = []): Blog
    {
        return Blog::create(array_replace($this->payload(), [
            'slug_normalized' => 'a13-test', 'translation_revision' => 'revision-1', 'is_faq' => false,
        ], $overrides));
    }

    public function test_retried_normalized_create_cannot_create_two_records(): void
    {
        $this->post('/admin/knowledge-hub/blogs', $this->createPayload())
            ->assertRedirect('/admin/knowledge-hub/blogs')->assertSessionHas('success');
        $this->from('/admin/knowledge-hub/blogs/create')->post('/admin/knowledge-hub/blogs', $this->createPayload(['slug' => '  A13 TEST  ']))
            ->assertRedirect('/admin/knowledge-hub/blogs/create')->assertSessionHasErrors('slug');
        $this->assertSame(1, Blog::count());
        Queue::assertPushed(TranslateBlogJob::class, 2);
        Queue::assertPushed(TranslateBlogJob::class, fn ($job) => $job->connection === 'blog-translations');
    }

    public function test_update_keeps_own_slug_and_chart_without_retranslating_unchanged_body(): void
    {
        $body = '<p><img src="data:image/png;base64,aGVsbG8=" alt="Chart" width="300"></p>';
        $blog = $this->record(['data' => $body]);
        $this->put('/admin/knowledge-hub/blogs/'.$blog->getKey(), $this->payload(['data' => $body]))
            ->assertRedirect('/admin/knowledge-hub/blogs')->assertSessionHas('success');
        $this->assertSame($body, $blog->fresh()->data);
        $this->assertSame('revision-1', $blog->fresh()->translation_revision);
        Queue::assertNothingPushed();
    }

    public function test_update_cannot_take_another_blog_slug(): void
    {
        $blog = $this->record();
        $other = $this->record(['slug' => 'other', 'slug_normalized' => 'other']);
        $this->put('/admin/knowledge-hub/blogs/'.$other->getKey(), $this->payload())
            ->assertSessionHasErrors('slug');
        $this->assertSame('other', $other->fresh()->slug);
        $this->assertSame(2, Blog::count());
    }

    public function test_empty_normalized_slug_is_rejected(): void
    {
        $this->post('/admin/knowledge-hub/blogs', $this->createPayload(['slug' => '!!!']))->assertSessionHasErrors('slug');
        $this->assertSame(0, Blog::count());
    }

    public function test_database_race_returns_friendly_slug_error(): void
    {
        Blog::creating(function (Blog $blog) {
            DB::connection('mongodb')->getDatabase()->selectCollection('blogs')->insertOne([
                'slug' => $blog->slug, 'slug_normalized' => $blog->slug_normalized,
            ]);
        });
        $this->post('/admin/knowledge-hub/blogs', $this->createPayload())->assertSessionHasErrors('slug');
        $this->assertSame(1, Blog::count());
        Queue::assertNothingPushed();
    }

    public function test_unrelated_unique_constraint_is_not_reported_as_slug_error(): void
    {
        $this->record();
        DB::connection('mongodb')->getDatabase()->selectCollection('blogs')->createIndex(['title' => 1], ['unique' => true, 'name' => 'unrelated_title_unique']);
        $this->withoutExceptionHandling();
        $this->expectException(BulkWriteException::class);
        $this->post('/admin/knowledge-hub/blogs', $this->createPayload(['slug' => 'different-slug']));
    }

    public function test_queue_failure_still_reports_that_source_was_saved(): void
    {
        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher->shouldReceive('dispatch')->once()->andThrow(new RuntimeException('Test queue unavailable'));
        $this->app->instance(Dispatcher::class, $dispatcher);
        $this->post('/admin/knowledge-hub/blogs', $this->createPayload())
            ->assertSessionHas('success')->assertSessionHas('warning');
        $this->assertSame(1, Blog::count());
    }

    public function test_migration_refuses_legacy_collisions_before_changing_records(): void
    {
        $collection = DB::connection('mongodb')->getDatabase()->selectCollection('blogs');
        $collection->insertMany([['slug' => 'Same_Slug'], ['slug' => 'same-slug']]);
        try {
            $this->migration()->up();
            $this->fail('Expected duplicate preflight failure');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('duplicate normalized', $exception->getMessage());
        }
        $this->assertSame(0, $collection->countDocuments(['slug_normalized' => ['$exists' => true]]));
    }

    public function test_migration_keeps_published_slug_and_backfills_normalized_key(): void
    {
        $collection = DB::connection('mongodb')->getDatabase()->selectCollection('blogs');
        $id = $collection->insertOne(['slug' => 'Published_Slug'])->getInsertedId();
        $this->migration()->up();
        $blog = $collection->findOne(['_id' => $id]);
        $this->assertSame('Published_Slug', $blog['slug']);
        $this->assertSame('published-slug', $blog['slug_normalized']);
    }

    public function test_real_concurrent_inserts_allow_only_one_normalized_slug(): void
    {
        $worker = <<<'PHP'
require $argv[1];
$db = (new MongoDB\Client('mongodb://127.0.0.1:27017'))->selectDatabase($argv[2]);
usleep(max(0, (int) (((float) $argv[3] - microtime(true)) * 1000000)));
try {
    $db->selectCollection('blogs')->insertOne(['slug' => 'concurrent', 'slug_normalized' => 'concurrent']);
    echo 'created';
} catch (MongoDB\Driver\Exception\BulkWriteException $e) {
    if ($e->getCode() !== 11000) throw $e;
    echo 'duplicate';
}
PHP;
        $start = (string) (microtime(true) + 1);
        $args = [PHP_BINARY, '-r', $worker, base_path('vendor/autoload.php'), $this->testDatabase, $start];
        $first = new Process($args);
        $second = new Process($args);
        $first->start();
        $second->start();
        $first->wait();
        $second->wait();
        $this->assertTrue($first->isSuccessful(), $first->getErrorOutput());
        $this->assertTrue($second->isSuccessful(), $second->getErrorOutput());
        $results = [$first->getOutput(), $second->getOutput()];
        sort($results);
        $this->assertSame(['created', 'duplicate'], $results);
        $this->assertSame(1, Blog::where('slug_normalized', 'concurrent')->count());
    }

    public function test_translation_job_discards_results_if_a_newer_edit_arrives(): void
    {
        $blog = $this->record();
        $translator = Mockery::mock(TranslationService::class);
        $translator->shouldReceive('translateText')->twice()->andReturnUsing(function () use ($blog) {
            $blog->update(['data' => '<p>New body</p>', 'translation_revision' => 'revision-2']);
            return 'Old translation';
        });
        (new TranslateBlogJob((string) $blog->getKey(), 'revision-1', 'fr'))->handle($translator);
        $this->assertNull($blog->fresh()->fr);
        $this->assertSame('<p>New body</p>', $blog->fresh()->data);
    }

    public function test_translation_updates_only_locale_and_leaves_source_chart_and_metadata(): void
    {
        $body = '<p><img src="/storage/chart.gif" alt="Chart"></p>';
        $blog = $this->record(['data' => $body, 'seo' => ['meta_title' => 'Keep me']]);
        $translator = Mockery::mock(TranslationService::class);
        $translator->shouldReceive('translateText')->with($blog->title, 'fr', 'en')->once()->andReturn('Titre');
        $translator->shouldReceive('translateText')->with($body, 'fr', 'en')->once()->andReturn($body);
        (new TranslateBlogJob((string) $blog->getKey(), 'revision-1', 'fr'))->handle($translator);
        $saved = $blog->fresh();
        $this->assertSame('Titre', $saved->fr['title']);
        $this->assertSame($body, $saved->data);
        $this->assertSame('Keep me', $saved->seo['meta_title']);
        $this->assertSame('a13-test', $saved->slug);
    }

    public function test_failed_translation_does_not_write_partial_locale(): void
    {
        $blog = $this->record();
        $translator = Mockery::mock(TranslationService::class);
        $translator->shouldReceive('translateText')->once()->andReturn(null);
        try {
            (new TranslateBlogJob((string) $blog->getKey(), 'revision-1', 'fr'))->handle($translator);
            $this->fail('Expected retryable failure');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('retry', $exception->getMessage());
        }
        $this->assertNull($blog->fresh()->fr);
    }

    public function test_durable_mongodb_queue_can_serialize_and_process_translation_job(): void
    {
        $blog = $this->record();
        $manager = new \Illuminate\Queue\QueueManager($this->app);
        $manager->addConnector('mongodb', fn () => new \MongoDB\Laravel\Queue\MongoConnector($this->app['db']));
        $queue = $manager->connection('blog-translations');
        $queue->push(new TranslateBlogJob((string) $blog->getKey(), 'revision-1', 'fr'));
        $this->assertSame(1, $queue->size());
        $reserved = $queue->pop();
        $this->assertNotNull($reserved);
        $payload = $reserved->payload();
        $job = unserialize($payload['data']['command']);
        $this->assertInstanceOf(TranslateBlogJob::class, $job);
        $translator = Mockery::mock(TranslationService::class);
        $translator->shouldReceive('translateText')->twice()->andReturn('Translated');
        $job->handle($translator);
        $reserved->delete();
        $this->assertSame(0, $queue->size());
        $this->assertSame('Translated', $blog->fresh()->fr['title']);
        $this->assertSame('<p>Body</p>', $blog->fresh()->data);
    }

    public function test_rejected_update_race_does_not_delete_existing_cover_or_social_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('blogs/original.png', 'original cover');
        Storage::disk('public')->put('blogs/social.png', 'original social image');
        $blog = $this->record([
            'image' => ['path' => 'blogs/original.png'],
            'seo' => ['og_image' => ['path' => 'blogs/social.png']],
        ]);
        Blog::updating(function (Blog $record) {
            DB::connection('mongodb')->getDatabase()->selectCollection('blogs')->insertOne([
                'slug' => $record->slug, 'slug_normalized' => $record->slug_normalized,
            ]);
        });
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        $this->put('/admin/knowledge-hub/blogs/'.$blog->getKey(), $this->payload([
            'slug' => 'raced-update',
            'image' => UploadedFile::fake()->createWithContent('replacement.png', $png),
            'seo_og_image' => UploadedFile::fake()->createWithContent('replacement-social.png', $png),
        ]))->assertSessionHasErrors('slug');
        Storage::disk('public')->assertExists('blogs/original.png');
        Storage::disk('public')->assertExists('blogs/social.png');
        $this->assertSame('a13-test', $blog->fresh()->slug);
        Queue::assertNothingPushed();
    }

    public function test_blog_form_renders_save_feedback_controls(): void
    {
        $role = \App\Models\Role::create([
            'role' => 'Test Editor', 'slug' => 'test-editor', 'can_access_admin' => true,
            'admin_permissions' => ['knowledge_hub.blogs'],
        ]);
        $user = \App\Models\User::create([
            'name' => 'Test Editor', 'email' => 'editor@example.test',
            'role_id' => $role->getKey(), 'is_active' => true,
        ]);
        $this->actingAs($user)->get('/admin/knowledge-hub/blogs/create')
            ->assertOk()->assertSee('blog-save-state.js', false)
            ->assertSee('id="blogSaveStatus"', false)->assertSee('data-save-label', false);
    }
}

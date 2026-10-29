<?php
namespace Tests\Feature;

use App\Models\News;
use Illuminate\Support\Facades\DB;
use App\Services\TranslationService;
use Tests\TestCase;

class NewsEditorTest extends TestCase
{
    private ?string $database = null;
    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('A23_MONGODB_TESTS') !== '1') $this->markTestSkipped('Requires isolated localhost MongoDB.');
        $this->database = 'pvmarket_a23_test_'.bin2hex(random_bytes(8));
        config(['database.connections.mongodb' => ['driver'=>'mongodb','dsn'=>'mongodb://127.0.0.1:27017','database'=>$this->database], 'languages.available'=>['en'=>'English'], 'session.driver'=>'array']);
        DB::purge('mongodb');
        $this->withoutMiddleware([\Illuminate\Auth\Middleware\Authenticate::class, \App\Http\Middleware\CheckAdminPermission::class]);
        $user = \Mockery::mock(\App\Models\User::class)->makePartial();
        $user->shouldReceive('hasAdminPermission')->andReturn(true);
        $user->shouldReceive('canAccessAdmin')->andReturn(true);
        $user->name = 'Local A23 tester';
        $this->actingAs($user);
        $translator = \Mockery::mock(TranslationService::class);
        $translator->shouldNotReceive('translateText');
        $this->app->instance(TranslationService::class, $translator);
    }
    protected function tearDown(): void
    {
        try { if ($this->database) { DB::connection('mongodb')->getDatabase()->drop(); DB::purge('mongodb'); } }
        finally { parent::tearDown(); }
    }
    public function test_existing_fields_load_and_unrelated_edit_preserves_article(): void
    {
        $body = '<h2>Existing heading</h2><p>Body <a href="https://example.test">link</a></p><p><img src="https://example.test/chart.png" alt="Chart"></p>';
        $news = News::create(['title'=>'Original title','slug'=>'original-address','content'=>'Summary','data'=>$body,'image'=>['url'=>'https://example.test/cover.png','path'=>'cover.png'],'seo'=>['meta_title'=>'Saved SEO','canonical_url'=>'https://example.test/news/original-address'],'alt_tag'=>'Original alt']);
        $this->get(route('admin.knowledge-hub.news.edit', $news->id))->assertOk()->assertSee('Original title')->assertSee(e($body), false);
        $this->get(route('admin.knowledge-hub.news.index'))->assertOk()->assertSee('Original title');
        $before = $news->toArray();
        $this->put(route('admin.knowledge-hub.news.update', $news->id), ['title'=>'Original title','slug'=>'original-address','content'=>'Summary','data'=>$body,'alt_tag'=>'Updated alt','seo_meta_title'=>'Saved SEO','seo_canonical_url'=>'https://example.test/news/original-address','seo_robots_index'=>'1','seo_robots_follow'=>'1'])->assertRedirect();
        $news->refresh();
        foreach (['title','slug','content','data','image'] as $field) $this->assertSame($before[$field], $news->$field, $field);
        $this->assertSame('Saved SEO', $news->seo['meta_title']);
        $this->assertSame('Updated alt', $news->alt_tag);
        $this->get(route('admin.knowledge-hub.news.edit', $news->id))->assertOk()->assertSee(e($body), false);
    }
    public function test_create_saves_full_body_and_heading_edit_preserves_address(): void
    {
        $this->post(route('admin.knowledge-hub.news.store'), ['title'=>'New article','data'=>'<p>Full article</p>','content'=>'Summary'])->assertRedirect();
        $news = News::firstOrFail();
        $this->assertSame('<p>Full article</p>', $news->data);
        $this->put(route('admin.knowledge-hub.news.update', $news->id), ['title'=>'Changed title'])->assertRedirect();
        $news->refresh();
        $this->assertSame('new-article', $news->slug);
        $this->assertSame('<p>Full article</p>', $news->data);
        $this->assertSame('Summary', $news->content);
    }
}

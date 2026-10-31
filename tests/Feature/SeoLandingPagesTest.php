<?php

namespace Tests\Feature;

use App\Models\MainMenu;
use App\Models\SeoMetaData;
use App\Services\SeoCacheInvalidator;
use App\Support\SeoLandingPages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SeoLandingPagesTest extends TestCase
{
    private ?string $database = null;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('SEO_LANDING_MONGODB_TESTS') !== '1') {
            $this->markTestSkipped('Requires isolated localhost MongoDB.');
        }
        $this->database = 'pvmarket_seo_landing_test_'.bin2hex(random_bytes(8));
        config(['database.connections.mongodb' => [
            'driver' => 'mongodb', 'dsn' => 'mongodb://127.0.0.1:27017', 'database' => $this->database,
        ], 'languages.available' => ['en' => 'English'], 'session.driver' => 'array']);
        DB::purge('mongodb');
        Http::preventStrayRequests();
        $this->withoutMiddleware([\Illuminate\Auth\Middleware\Authenticate::class, \App\Http\Middleware\CheckAdminPermission::class]);
        $user = \Mockery::mock(\App\Models\User::class)->makePartial();
        $user->shouldReceive('hasAdminPermission')->andReturn(true);
        $user->shouldReceive('canAccessAdmin')->andReturn(true);
        $user->name = 'Local SEO tester';
        $this->actingAs($user);
        $invalidator = \Mockery::mock(SeoCacheInvalidator::class);
        $invalidator->shouldReceive('invalidate')->andReturn(false);
        $this->app->instance(SeoCacheInvalidator::class, $invalidator);
    }

    protected function tearDown(): void
    {
        try {
            if ($this->database) {
                DB::connection('mongodb')->getDatabase()->drop();
                DB::purge('mongodb');
            }
        } finally { parent::tearDown(); }
    }

    private function payload(string $key): array
    {
        return [
            'category_id' => 'page:'.$key, 'meta_title' => 'Saved '.$key,
            'meta_description' => 'Search summary', 'page_header' => 'Visible heading',
            'short_description' => 'Introduction', 'bottom_header' => 'More information',
            'bottom_description' => '<p>Saved <a href="https://example.test">body link</a></p>',
            'faqs' => [['question' => 'Question?', 'answer' => '<p>Answer</p>']],
            'internal_links' => [['title' => 'Related', 'url' => 'https://example.test/related']],
            'canonical_url' => 'https://pv.market'.SeoLandingPages::PAGES[$key]['path'],
            'og_type' => 'website', 'og_title' => 'Social title', 'twitter_title' => 'X title',
            'max_image_preview' => 'large', 'robot_index' => '1', 'robot_follow' => '1',
        ];
    }

    public function test_each_global_page_saves_reopens_updates_and_deletes_without_touching_home(): void
    {
        $home = SeoMetaData::create(['meta_title' => 'Existing home', 'is_active' => true]);
        foreach (SeoLandingPages::PAGES as $key => $page) {
            $payload = $this->payload($key);
            $this->post(route('admin.seo-meta.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
            $record = SeoMetaData::where('page_key', $key)->firstOrFail();
            $this->assertSame('global', $record->market_code);
            $this->assertNull($record->category_id);
            $this->assertSame($page['label'], $record->page_type);
            $this->get(route('admin.seo-meta.edit', $record->id))->assertOk()
                ->assertSee('Visible heading')->assertSee('body link')->assertSee('Question?');
            $this->assertSame('Social title', $record->ogMeta->og_title);
            $this->assertSame('X title', $record->twitterMeta->twitter_title);
            $this->assertTrue($record->robotMeta->index);
            $this->post(route('admin.seo-meta.store'), $payload)->assertSessionHasErrors();
            $this->assertSame(1, SeoMetaData::where('page_key', $key)->count());
            $payload['page_header'] = 'Updated heading';
            unset($payload['category_id']);
            $payload['page_key'] = $key;
            $this->put(route('admin.seo-meta.update', $record->id), $payload)->assertSessionHasNoErrors()->assertRedirect();
            $record->refresh();
            $this->assertSame('global', $record->market_code);
            $this->assertSame($payload['bottom_description'], $record->bottom_description);
            $this->assertSame($payload['faqs'], $record->faqs);
            $this->assertSame($payload['internal_links'], $record->internal_links);
            $this->get(route('admin.seo-meta.edit', $record->id))->assertOk()->assertSee('Updated heading');
            $this->get(route('admin.seo-meta.index', ['type' => 'landing']))->assertOk()->assertSee('Saved '.$key)->assertDontSee('Existing home');
            $this->get(route('admin.seo-meta.index', ['type' => 'home']))->assertOk()->assertSee('Existing home')->assertDontSee('Saved '.$key);
            $this->delete(route('admin.seo-meta.destroy', $record->id))->assertRedirect();
            $this->assertFalse($record->fresh()->is_active);
        }
        $this->assertSame('Existing home', $home->fresh()->meta_title);
    }

    public function test_invalid_market_entity_and_page_selection_are_rejected(): void
    {
        foreach (['market_id', 'category_id', 'sub_category_id', 'brand_id', 'product_id'] as $field) {
            $this->post(route('admin.seo-meta.store'), [...$this->payload('brands'), 'category_id' => null, 'page_key' => 'brands', $field => '6ab3c281b4ea08bca90acd32'])
                ->assertSessionHasErrors($field);
        }
        $this->post(route('admin.seo-meta.store'), [...$this->payload('brands'), 'category_id' => 'page:about'])
            ->assertSessionHasErrors('page_key');
        $this->assertSame(0, SeoMetaData::count());
        $static = SeoMetaData::create(['page_key' => 'about', 'is_active' => true, 'meta_title' => 'Static about']);
        $this->get(route('admin.seo-meta.edit', $static->id))->assertNotFound();
        $this->put(route('admin.seo-meta.update', $static->id), [...$this->payload('brands'), 'category_id' => null, 'page_key' => 'brands'])->assertSessionHasErrors('page_key');
        $this->delete(route('admin.seo-meta.destroy', $static->id))->assertNotFound();
        $this->get(route('admin.seo-meta.index'))->assertOk()->assertDontSee('Static about');
    }

    public function test_none_with_brand_saves_a_distinct_brand_only_record(): void
    {
        $brand = \App\Models\Brand::create(['name' => 'JA Solar', 'slug' => 'ja-solar', 'is_active' => true]);
        $this->post(route('admin.seo-meta.store'), $this->payload('brands'))->assertSessionHasNoErrors();
        $payload = [...$this->payload('brands'), 'category_id' => '', 'brand_id' => (string) $brand->id,
            'meta_title' => 'JA Solar SEO', 'canonical_url' => 'https://pv.market/ja-solar'];
        $this->post(route('admin.seo-meta.store'), $payload)->assertSessionHasNoErrors();
        $record = SeoMetaData::forEntity(null, null, null, (string) $brand->id)->firstOrFail();
        $this->assertEmpty($record->page_key);
        $this->assertSame('ja-solar', $record->brand_slug);
        $this->assertSame('global', $record->market_code);
        $this->assertSame('https://pv.market/ja-solar', $record->canonical_url);
        $this->get(route('admin.seo-meta.edit', $record->id))->assertOk()->assertSee('JA Solar SEO');
        $update = $payload;
        unset($update['category_id'], $update['brand_id']);
        $update['page_header'] = 'JA Solar updated heading';
        $this->put(route('admin.seo-meta.update', $record->id), $update)->assertSessionHasNoErrors();
        $record->refresh();
        $this->assertSame('global', $record->market_code);
        $this->assertSame('ja-solar', $record->brand_slug);
        $this->assertSame('JA Solar updated heading', $record->page_header);
        $this->post(route('admin.seo-meta.store'), $payload)->assertSessionHasErrors();
        $this->assertSame(1, SeoMetaData::where('page_key', 'brands')->count());
        $this->assertSame(2, SeoMetaData::count());
        $this->post(route('admin.seo-meta.store'), [...$payload, 'category_id' => 'page:all_categories'])->assertSessionHasErrors('brand_id');
        $this->post(route('admin.seo-meta.store'), [...$payload, 'category_id' => 'page:brands'])->assertSessionHasErrors('brand_id');
    }

    public function test_seo_categories_include_active_non_stock_categories_but_shared_dropdown_does_not(): void
    {
        MainMenu::create(['category_name' => 'Stock category', 'slug' => 'stock', 'is_active' => true, 'stock_value' => true]);
        MainMenu::create(['category_name' => 'Non stock category', 'slug' => 'non-stock', 'is_active' => true, 'stock_value' => false]);
        MainMenu::create(['category_name' => 'Inactive category', 'slug' => 'inactive', 'is_active' => false, 'stock_value' => true]);
        $this->get(route('admin.seo-meta.create'))->assertOk()
            ->assertSee('Stock category')->assertSee('Non stock category')->assertDontSee('Inactive category')
            ->assertSee('All Categories')->assertSee('Discount Center');
        $this->get(route('admin.seo-meta.create'))->assertDontSee('id="page_key"', false)
            ->assertSee('value="page:brands"', false);
        $this->assertSame(['Stock category'], MainMenu::availableForDropdown()->pluck('category_name')->all());
        $this->post(route('admin.seo-meta.store'), [...$this->payload('brands'), 'category_id' => 'page:home'])->assertSessionHasNoErrors();
        $home = SeoMetaData::forEntity()->firstOrFail();
        $this->get(route('admin.seo-meta.edit', $home->id))->assertOk()->assertViewHas('categories',
            fn ($categories) => $categories->pluck('category_name')->all() === ['Non stock category', 'Stock category']);
    }

    public function test_none_does_not_create_home_and_explicit_home_preserves_legacy_identity(): void
    {
        $payload = [...$this->payload('brands'), 'category_id' => ''];
        $this->post(route('admin.seo-meta.store'), $payload)->assertSessionHasErrors('category_id');
        $this->post(route('admin.seo-meta.store'), [...$payload, 'brand_id' => 'invalid'])->assertSessionHasErrors('brand_id');
        $inactive = \App\Models\Brand::create(['name' => 'Inactive', 'slug' => 'inactive', 'is_active' => false]);
        $this->post(route('admin.seo-meta.store'), [...$payload, 'brand_id' => (string) $inactive->id])->assertSessionHasErrors('brand_id');
        foreach (['brand_id', 'sub_category_id', 'product_id'] as $field) {
            $this->post(route('admin.seo-meta.store'), [...$payload, 'category_id' => 'page:home', $field => '6ab3c281b4ea08bca90acd32'])->assertSessionHasErrors($field);
        }
        $this->assertSame(0, SeoMetaData::count());
        $home = SeoMetaData::create(['meta_title' => 'Legacy homepage', 'is_active' => true]);
        $this->post(route('admin.seo-meta.store'), [...$payload, 'category_id' => 'page:home'])->assertSessionHasErrors();
        $this->assertSame(1, SeoMetaData::count());
        $this->get(route('admin.seo-meta.edit', $home->id))->assertOk()
            ->assertViewHas('record', fn ($record) => $record->meta_title === 'Legacy homepage');
        unset($payload['category_id']);
        $payload['meta_title'] = 'Updated legacy homepage';
        $this->put(route('admin.seo-meta.update', $home->id), $payload)->assertSessionHasNoErrors();
        $home->refresh();
        $this->assertEmpty($home->page_key);
        $this->assertNull($home->brand_id);
        $this->assertNull($home->category_id);
        $this->assertSame('Updated legacy homepage', $home->meta_title);
    }
}

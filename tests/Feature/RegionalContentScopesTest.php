<?php

namespace Tests\Feature;

use App\Models\MainMenu;
use App\Models\SeoMetaData;
use App\Services\SeoCacheInvalidator;
use App\Support\SeoLandingPages;
use App\Support\SeoStaticPages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegionalContentScopesTest extends TestCase
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
        $views = storage_path('framework/testing/'.$this->database);
        if (!is_dir($views)) mkdir($views, 0777, true);
        config(['view.compiled'=>$views]);
        Http::preventStrayRequests();
        $translator=\Mockery::mock(\App\Services\TranslationService::class)->makePartial();
        $translator->shouldReceive('translateText')->andReturnUsing(fn($text)=>$text);
        $this->app->instance(\App\Services\TranslationService::class,$translator);
        foreach (['europe'=>['FR'],'asia'=>['IN']] as $code=>$countries) {
            DB::connection('mongodb')->getCollection('stock_regions')->insertOne(['_id'=>$code,'name'=>ucfirst($code),'countries'=>$countries,'is_active'=>true]);
        }
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

    private function regionPayload(string $scope, string $title): array
    {
        return ['market_id'=>$scope, 'category_id'=>'page:brands', 'meta_title'=>$title, 'og_type'=>'website', 'max_image_preview'=>'large', 'robot_index'=>'1', 'robot_follow'=>'1'];
    }

    public function test_regional_seo_isolated_from_global_and_duplicate_checks(): void
    {
        $global = SeoMetaData::create(['page_key'=>'brands', 'market_code'=>'global', 'meta_title'=>'Global brands', 'is_active'=>true]);
        foreach (['europe','asia'] as $region) {
            $this->post(route('admin.seo-meta.store'), $this->regionPayload('region:'.$region, $region.' brands'))->assertRedirect();
            $row = SeoMetaData::where('region_code', $region)->firstOrFail();
            $this->assertSame('region:'.$region, $row->market_code);
            $this->assertNull($row->market_id);
            $this->post(route('admin.seo-meta.store'), $this->regionPayload('region:'.$region, 'duplicate'))->assertRedirect(route('admin.seo-meta.edit', $row->id));
            $this->get(route('admin.seo-meta.edit', $row->id))->assertOk()->assertSee($region.' brands');
        }
        $this->assertSame('Global brands', $global->fresh()->meta_title);
        $this->assertSame(3, SeoMetaData::where('page_key','brands')->count());
        $country = \App\Models\Market::create(['name'=>'India', 'code'=>'in','is_active'=>true]);
        $this->post(route('admin.seo-meta.store'), $this->regionPayload((string)$country->id, 'India brands'))->assertRedirect();
        $this->assertSame('India brands', SeoMetaData::where('market_code','in')->firstOrFail()->meta_title);
        $this->assertSame('europe brands', SeoMetaData::where('region_code','europe')->firstOrFail()->meta_title);
        DB::connection('mongodb')->getCollection('stock_regions')->insertOne(['_id'=>'disabled-test','name'=>'Disabled','countries'=>['IN'],'is_active'=>false]);
        $this->post(route('admin.seo-meta.store'), $this->regionPayload('region:disabled-test', 'Invalid'))->assertSessionHasErrors('market_id');

        $this->get(route('admin.seo-meta.create'))->assertOk()->assertSee('Regions')->assertSee('region:europe');
        $this->get(route('admin.seo-meta.index', ['market_id'=>'global']))->assertOk()->assertSee('Global brands')->assertDontSee('europe brands');
    }

    public function test_static_copy_publish_and_updates_are_scoped_independently(): void
    {
        $global = \App\Models\PageSection::create(['page'=>'home','section'=>'hero','title'=>'Global hero','is_active'=>true,'location_id'=>null]);
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::disk('public')->put('shared-hero.png','shared image');
        $global->forceFill(['fr'=>['title'=>'Titre global'], 'image'=>'shared-hero.png'])->save();
        \App\Models\PageSetting::create(['page'=>'home','location_id'=>null,'is_published'=>true]);
        foreach (['europe','asia'] as $region) {
            $selection = 'region:'.$region;
            $this->post(route('admin.page-sections.copy-from', $selection), ['source_market_id'=>'global'])->assertRedirect();
            $section = \App\Models\PageSection::where('region_code',$region)->firstOrFail();
            $this->get(route('admin.page-sections.edit', [$selection,'home']))->assertOk()->assertSee('Global hero');
            $this->put(route('admin.page-sections.update', [$selection,'home']), ['is_published'=>'1','sections'=>[
                (string)$section->id=>['title'=>$region.' hero','is_active'=>'1'],
                (string)$global->id=>['title'=>'Must not overwrite Global','is_active'=>'1'],
            ]])->assertRedirect();
            $this->assertSame($region.' hero', $section->fresh()->title);
            $this->assertSame('Titre global', $section->fresh()->fr['title']);
            $this->assertTrue(\App\Models\PageSetting::where('region_code',$region)->firstOrFail()->is_published);
        }
        $europe = \App\Models\PageSection::where('region_code','europe')->firstOrFail();
        $this->put(route('admin.page-sections.update', ['region:europe','home']), ['is_published'=>'1','sections'=>[
            (string)$europe->id=>['title'=>'europe hero','is_active'=>'1','image'=>\Illuminate\Http\UploadedFile::fake()->create('replacement.png',1,'image/png')],
        ]])->assertRedirect();
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists('shared-hero.png');
        $this->assertNotSame('shared-hero.png',$europe->fresh()->image);
        $this->assertSame('Global hero', $global->fresh()->title);
        $this->get(route('admin.page-sections.edit', ['global','home']))->assertOk()->assertSee('Global hero')->assertDontSee('europe hero');
        $this->post(route('admin.page-sections.copy-from', 'region:europe'), ['source_market_id'=>'region:europe'])->assertSessionHas('error');
        $this->assertSame('europe hero', \App\Models\PageSection::where('region_code','europe')->firstOrFail()->title);
    }
    public function test_seo_regions_only_include_eligible_database_records(): void
    {
        $collection=DB::connection('mongodb')->getCollection('stock_regions');
        $collection->deleteMany([]);
        $this->assertSame([], \App\Support\StorefrontScopes::options(true)['Regions']);
        $collection->insertMany([
            ['_id'=>'europe','name'=>'Europe from database','countries'=>['FR'],'is_active'=>true],
            ['_id'=>'gulf','name'=>'Gulf','countries'=>['AE'],'is_active'=>true],
            ['_id'=>'africa','name'=>'Africa','countries'=>['NG'],'is_active'=>false],
            ['_id'=>'asia','name'=>'Asia','countries'=>['IN'],'is_active'=>true,'is_deleted'=>true],
            ['_id'=>'oceania','name'=>'Oceania','countries'=>[],'is_active'=>true],
        ]);
        $options=\App\Support\StorefrontScopes::options(true)['Regions'];
        $this->assertSame(['region:europe'=>'Europe from database','region:gulf'=>'Gulf'],$options);
        $this->assertSame('/europe',\App\Support\StorefrontScopes::region('region:europe',true)['path']);
        $this->assertSame('/region/gulf',\App\Support\StorefrontScopes::region('region:gulf',true)['path']);
        $response=$this->get(route('admin.seo-meta.create'))->assertOk();
        $response->assertSee('value="region:europe"',false)->assertSee('value="region:gulf"',false);
        foreach (['north-america','south-america','africa','asia','oceania'] as $code) {
            $response->assertDontSee('value="region:'.$code.'"',false);
            $this->post(route('admin.seo-meta.store'),$this->regionPayload('region:'.$code,'Invalid'))->assertSessionHasErrors('market_id');
        }
        $this->assertArrayHasKey('north-america',\App\Support\StorefrontScopes::regions());
    }
}

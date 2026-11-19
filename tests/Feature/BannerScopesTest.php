<?php
namespace Tests\Feature;

use App\Models\Market;
use App\Models\Slider;
use App\Models\Advertisement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerScopesTest extends TestCase
{
 private ?string $database=null;
 protected function setUp(): void {
  parent::setUp();if(getenv('BANNER_SCOPES_MONGODB_TESTS')!=='1')$this->markTestSkipped('Requires disposable localhost MongoDB.');
  $this->database='pvmarket_banner_admin_test_'.bin2hex(random_bytes(8));
  config(['database.connections.mongodb'=>['driver'=>'mongodb','dsn'=>'mongodb://127.0.0.1:27017','database'=>$this->database],'languages.available'=>['en'=>'English'],'session.driver'=>'array']);DB::purge('mongodb');
  $views=storage_path('framework/testing/'.$this->database);if(!is_dir($views))mkdir($views,0777,true);config(['view.compiled'=>$views]);
  $this->withoutMiddleware([\Illuminate\Auth\Middleware\Authenticate::class,\App\Http\Middleware\CheckAdminPermission::class]);
  $user=\Mockery::mock(\App\Models\User::class)->makePartial();$user->shouldReceive('hasAdminPermission')->andReturn(true);$user->shouldReceive('canAccessAdmin')->andReturn(true);$user->name='Banner tester';$this->actingAs($user);
  $translator=\Mockery::mock(\App\Services\TranslationService::class)->makePartial();$translator->shouldNotReceive('translateText');$this->app->instance(\App\Services\TranslationService::class,$translator);
  DB::connection('mongodb')->getCollection('stock_regions')->insertMany([['_id'=>'africa','name'=>'Africa','countries'=>['NG'],'is_active'=>true],['_id'=>'gulf','name'=>'Gulf','countries'=>['AE'],'is_active'=>true],['_id'=>'disabled','name'=>'Disabled','countries'=>['NG'],'is_active'=>false]]);
  Storage::fake('public');
 }
 protected function tearDown(): void {try{if($this->database)DB::connection('mongodb')->getDatabase()->drop();DB::purge('mongodb');}finally{parent::tearDown();}}
 public function test_slider_bulk_create_scope_edit_order_and_active_status(): void {
  $global=Market::create(['name'=>'Global','code'=>'global','is_active'=>true]);$ng=Market::create(['name'=>'Nigeria','code'=>'ng','is_active'=>true]);
  $this->post(route('admin.setup.sliders.store'),['sliders'=>[
   ['name'=>'Africa top','location_id'=>'region:africa','slider_type'=>'top'],
   ['name'=>'Global top','location_id'=>'global','slider_type'=>'top'],
   ['name'=>'Nigeria ad slider','location_id'=>(string)$ng->getKey(),'slider_type'=>'advertisement'],
  ]])->assertSessionHasNoErrors()->assertRedirect();
  $africa=Slider::where('name','Africa top')->firstOrFail();$this->assertSame('africa',$africa->region_code);$this->assertNull($africa->location_id);
  $this->assertSame((string)$global->getKey(),(string)Slider::where('name','Global top')->firstOrFail()->location_id);
  $this->get(route('admin.setup.sliders.edit',$africa->id))->assertOk()->assertSee('value="region:africa" selected',false);
  $this->get(route('admin.setup.sliders.index'))->assertOk()->assertSee('Africa');
  Storage::disk('public')->put('sliders/keep.png','fixture');$africa->update(['image'=>'sliders/keep.png']);
  $this->put(route('admin.setup.sliders.update',$africa->id),['name'=>'Africa top','location_id'=>(string)$ng->getKey(),'slider_type'=>'top'])->assertSessionHasNoErrors()->assertRedirect();
  $this->assertNull($africa->fresh()->region_code);$this->assertSame((string)$ng->getKey(),(string)$africa->fresh()->location_id);Storage::disk('public')->assertExists('sliders/keep.png');
  $this->post(route('admin.setup.sliders.reorder'),['order'=>[(string)$africa->id]])->assertOk();$this->assertSame(1,$africa->fresh()->sort_order);
  $this->patch(route('admin.setup.sliders.toggle',$africa->id))->assertRedirect();$this->assertFalse($africa->fresh()->is_active);
 }
 public function test_advertisement_scopes_and_grouped_dropdowns(): void {
  $global=Market::create(['name'=>'Global','code'=>'global','is_active'=>true]);Market::create(['name'=>'Nigeria','code'=>'ng','is_active'=>true]);
  $this->post(route('admin.setup.advertisements.store'),['title'=>'Africa standalone','location_id'=>'region:africa'])->assertSessionHasNoErrors()->assertRedirect();
  $ad=Advertisement::firstOrFail();$this->assertSame('africa',$ad->region_code);$this->assertNull($ad->location_id);
  $this->get(route('admin.setup.advertisements.edit',$ad->id))->assertOk()->assertSee('value="region:africa" selected',false);
  foreach(['sliders','advertisements'] as $resource){
   $response=$this->get(route('admin.setup.'.$resource.'.create'))->assertOk();
   $response->assertSee('label="Regions"',false)->assertSee('label="Countries"',false)->assertSee('region:africa')->assertSee('region:gulf')->assertDontSee('region:north-america')->assertDontSee('region:disabled');
  }
  $this->put(route('admin.setup.advertisements.update',$ad->id),['title'=>'Global standalone','location_id'=>'global'])->assertSessionHasNoErrors()->assertRedirect();
  $this->assertNull($ad->fresh()->region_code);$this->assertSame((string)$global->getKey(),(string)$ad->fresh()->location_id);
  $this->get(route('admin.setup.advertisements.edit',$ad->id))->assertOk()->assertSee('value="global" selected',false);
 }
 public function test_invalid_scope_does_not_partially_create_slider_batch(): void {
  $this->post(route('admin.setup.sliders.store'),['sliders'=>[['name'=>'Valid','location_id'=>'region:africa','slider_type'=>'top'],['name'=>'Invalid','location_id'=>'region:disabled','slider_type'=>'top']]])->assertSessionHasErrors('sliders.1.location_id');
  $this->assertSame(0,Slider::count());
  $this->post(route('admin.setup.advertisements.store'),['title'=>'Missing','location_id'=>'region:missing'])->assertSessionHasErrors('location_id');
  $this->assertSame(0,Advertisement::count());
 }
}

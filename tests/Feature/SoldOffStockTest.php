<?php
namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductListing;
use App\Models\InventoryTransaction;
use App\Services\TranslationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Mockery;
use MongoDB\BSON\ObjectId;
use Tests\TestCase;

class SoldOffStockTest extends TestCase
{
    private ?string $testDatabase = null;
    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('SOLD_OFF_MONGODB_TESTS') !== '1') $this->markTestSkipped('Disposable localhost MongoDB opt in required.');
        $this->testDatabase = 'pvmarket_soldoff_test_'.bin2hex(random_bytes(8));
        config(['database.connections.mongodb' => ['driver'=>'mongodb','dsn'=>'mongodb://127.0.0.1:27017','database'=>$this->testDatabase], 'session.driver'=>'array']);
        DB::purge('mongodb');
        $this->withoutMiddleware(); Mail::fake(); Queue::fake();
        $this->app->instance(TranslationService::class, Mockery::mock(TranslationService::class));
        Auth::shouldReceive('user')->andReturn(null);
        Auth::shouldReceive('id')->andReturn((string)new ObjectId);
    }
    protected function tearDown(): void
    {
        try {
            if ($this->testDatabase && str_starts_with($this->testDatabase,'pvmarket_soldoff_test_')) {
                DB::connection('mongodb')->getDatabase()->drop(); DB::purge('mongodb');
            }
        } finally { parent::tearDown(); }
    }
    public function test_sold_off_clears_stock_preserves_price_and_requires_explicit_restock(): void
    {
        $product = Product::create(['product_name'=>'Local stock test']);
        $listing = ProductListing::create(['user_id'=>new ObjectId,'product_id'=>new ObjectId((string)$product->_id),'warehouse_id'=>new ObjectId,'main_category_id'=>new ObjectId,'sub_category_id'=>new ObjectId,'currency_id'=>'USD','sell_type'=>'sell by pieces','total_quantity'=>10,'is_sold_off'=>false,'slug'=>'local-stock-test','slots'=>[]]);
        $payload = ['product_id'=>(string)$product->_id,'warehouse_id'=>(string)$listing->warehouse_id,'main_category_id'=>(string)$listing->main_category_id,'sub_category_id'=>(string)$listing->sub_category_id,'currency_id'=>'USD','incoterm_id'=>(string)new ObjectId,'sell_type'=>'sell by pieces','total_quantity'=>10,'lead_time'=>1,'is_sold_off'=>1,'slots'=>[['min_quantity'=>4,'max_quantity'=>null,'price'=>3050]]];
        $url='/user/listings/'.$listing->_id;
        $this->put($url,$payload)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(0,(int)$listing->fresh()->total_quantity);
        $this->assertTrue((bool)$listing->fresh()->is_sold_off);
        $movement=InventoryTransaction::first();
        $this->assertSame(10,(int)$movement->quantity_before);
        $this->assertSame(0,(int)$movement->quantity_after);
        $this->assertSame('Marked as Sold Off',$movement->notes);
        $this->put($url,$payload)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(1,InventoryTransaction::count());
        $payload['is_sold_off']=0; $payload['total_quantity']=0;
        $this->put($url,$payload)->assertSessionHasErrors('total_quantity');
        $this->flushSession();
        $payload['total_quantity']=20; $payload['inventory_notes']='Restocked';
        $this->put($url,$payload)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(20,(int)$listing->fresh()->total_quantity);
        $this->assertFalse((bool)$listing->fresh()->is_sold_off);
        $this->assertSame(2,InventoryTransaction::count());
    }
}

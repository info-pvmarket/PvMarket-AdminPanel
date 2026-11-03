<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductListing;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ListingTierUnitsTest extends TestCase
{
    private ?string $database = null;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('LISTING_TIER_MONGODB_TESTS') !== '1') {
            $this->markTestSkipped('Requires isolated localhost MongoDB.');
        }
        $this->database = 'pvmarket_tier_test_'.bin2hex(random_bytes(8));
        config(['database.connections.mongodb' => [
            'driver' => 'mongodb', 'dsn' => 'mongodb://127.0.0.1:27017', 'database' => $this->database,
        ], 'session.driver' => 'array']);
        DB::purge('mongodb');
        Http::preventStrayRequests();
        Mail::fake();
        $this->withoutMiddleware([\Illuminate\Auth\Middleware\Authenticate::class, \App\Http\Middleware\CheckAdminPermission::class]);
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasAdminPermission')->andReturn(true);
        $user->shouldReceive('canAccessAdmin')->andReturn(true);
        $user->shouldReceive('isSuperAdmin')->andReturn(false);
        $user->id = (string) new \MongoDB\BSON\ObjectId();
        $user->name = 'Tier tester';
        $user->email = 'tier@example.test';
        $this->actingAs($user);
    }

    protected function tearDown(): void
    {
        try {
            if ($this->database !== null) {
                DB::connection('mongodb')->getDatabase()->drop();
                DB::purge('mongodb');
            }
        } finally { parent::tearDown(); }
    }

    public function test_create_reopen_and_repeat_update_keep_tiers_in_canonical_pieces(): void
    {
        $product = Product::create(['product_name' => 'Local packing control', 'pieces_per_pallet' => 36, 'pallets_per_container' => 26]);
        foreach (['sell by pieces' => 1, 'sell by pallets' => 36, 'sell by container' => 936] as $type => $factor) {
            $payload = [
                'product_id' => (string) $product->id,
                'main_category_id' => (string) new \MongoDB\BSON\ObjectId(),
                'sub_category_id' => (string) new \MongoDB\BSON\ObjectId(),
                'warehouse_id' => (string) new \MongoDB\BSON\ObjectId(),
                'incoterm_id' => (string) new \MongoDB\BSON\ObjectId(),
                'currency_id' => 'USD', 'sell_type' => $type, 'total_quantity' => 10, 'lead_time' => 1,
                'slots' => [
                    ['min_quantity' => 2, 'max_quantity' => 5, 'price' => 100, 'commission_percentage' => 3],
                    ['min_quantity' => 6, 'max_quantity' => '', 'price' => 90],
                ],
            ];
            $this->post(route('product_listing.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
            $listing = ProductListing::orderBy('_id', 'desc')->firstOrFail();
            for ($save = 0; $save < 2; $save++) {
                $listing->refresh();
                $slots = $listing->slots;
                $this->assertEquals(2 * $factor, data_get($slots, '0.min_quantity'));
                $this->assertEquals(5 * $factor, data_get($slots, '0.max_quantity'));
                $this->assertNull(data_get($slots, '1.max_quantity'));
                $this->assertEquals(10 * $factor, $listing->total_quantity);
                $this->assertEquals(103, data_get($slots, '0.total_price'));
                $response = $this->get(route('product_listing.edit', $listing->id))->assertOk();
                $display = $response->viewData('displaySlots');
                $this->assertEquals(2, $display[0]['min_quantity']);
                $this->assertEquals(5, $display[0]['max_quantity']);
                $payload['slots'] = $display;
                $this->put(route('product_listing.update', $listing->id), $payload)->assertSessionHasNoErrors()->assertRedirect();
            }
            $payload['slots'][0]['min_quantity'] = 11;
            $this->put(route('product_listing.update', $listing->id), $payload)->assertSessionHasErrors('slots.0.min_quantity');
            $payload['slots'][0]['min_quantity'] = 1.5;
            $this->put(route('product_listing.update', $listing->id), $payload)->assertSessionHasErrors('slots.0.min_quantity');
        }
    }

    public function test_legacy_partial_pallet_is_not_silently_truncated(): void
    {
        $product = new Product(['pieces_per_pallet' => 36, 'pallets_per_container' => 26]);
        $display = ProductListing::tierQuantitiesForDisplay([['min_quantity' => 31, 'max_quantity' => null]], 'sell by pallets', $product);
        $this->assertEqualsWithDelta(31 / 36, $display[0]['min_quantity'], 0.00000001);
        $this->assertNull($display[0]['max_quantity']);
    }
}

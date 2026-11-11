<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;

return new class extends Migration
{
    public function up(): void
    {
        $database = DB::connection('mongodb')->getDatabase();
        $products = $database->selectCollection('products');
        $listings = $database->selectCollection('product_listings');
        $productId = new ObjectId('6a630af64ad0c3d7f6022c15');
        $listingId = new ObjectId('6a63157cbe5bc5a6960a854b');
        $oldName = 'Eastups EA5KSI 5kW Single-Phase Hybrid Solar Inverter | Dual MPPT | IP65';
        $newName = 'Eastups EA5KSI 5kW Single-Phase On-Grid Solar Inverter | Dual MPPT | IP65';
        $oldSlug = 'eastups-ea5ksi-5kw-single-phase-hybrid-solar-inverter-dual-mppt-ip65';
        $newSlug = 'eastups-ea5ksi-5kw-single-phase-on-grid-solar-inverter-dual-mppt-ip65';

        $product = $products->findOne(['_id' => $productId, 'product_name' => ['$in' => [$oldName, $newName]]]);
        if (!$product) {
            return;
        }

        $listing = ['_id' => $listingId, 'product_id' => $productId, 'slug' => $oldSlug];
        $rename = $listings->findOne($listing) && !$listings->findOne([
            '_id' => ['$ne' => $listingId],
            '$or' => [['slug' => $newSlug], ['slug_aliases' => $newSlug]],
        ]);

        $products->updateOne(['_id' => $productId, 'product_name' => $oldName], ['$set' => [
            'product_name' => $newName,
            'product_description' => '<p>The Eastups EA5KSI is a single-phase, grid-connected solar PV inverter rated at 5 kW in the manufacturer’s European specification. It has two MPPTs, a 90–550 V MPPT voltage range, an IP65 enclosure and natural cooling.</p>',
        ]]);

        if ($rename) {
            $listings->updateOne($listing, [
                '$set' => ['slug' => $newSlug],
                '$addToSet' => ['slug_aliases' => $oldSlug],
            ]);
        }
    }

    public function down(): void
    {
        // Keep the corrected public copy and existing URL alias on rollback.
    }
};

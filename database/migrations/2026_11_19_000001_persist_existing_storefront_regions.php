<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $collection = DB::connection('mongodb')->getCollection('stock_regions');
        foreach (config('stock_regions', []) as $code => $countries) {
            // Insert only missing defaults. Saved edits, disabled regions and deletion
            // tombstones must always win over configuration.
            $collection->updateOne(['_id' => $code], ['$setOnInsert' => [
                'name' => str($code)->replace('-', ' ')->title()->toString(),
                'countries' => array_values(array_filter(explode(' ', $countries))),
                'is_active' => true,
            ]], ['upsert' => true]);
        }
    }

    public function down(): void
    {
        // These are existing storefront regions, now persisted for shared selectors.
        // Never delete administrator-owned configuration during a code rollback.
    }
};

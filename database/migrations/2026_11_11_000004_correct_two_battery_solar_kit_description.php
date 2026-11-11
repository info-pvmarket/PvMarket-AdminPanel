<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection('mongodb')->getDatabase()->selectCollection('products')->updateOne(
            [
                '_id' => new ObjectId('6a6300bd4ad0c3d7f602296e'),
                'product_name' => '5Kw Rooftop solar kit with 2 Li-ion battery & Solis Inverter',
                'product_description' => '<p>Ready to install 5kw rooftop kit with 1 Li-ion battery</p>',
            ],
            ['$set' => [
                'product_description' => '<p>Ready-to-install 5 kW rooftop solar kit with a Solis inverter and two lithium-ion batteries. See the listing for product specifications and available offers.</p>',
            ]],
        );
    }

    public function down(): void
    {
        // Preserve the corrected description on rollback.
    }
};

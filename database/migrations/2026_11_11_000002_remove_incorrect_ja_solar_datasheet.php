<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;

return new class extends Migration
{
    public function up(): void
    {
        // Seven listings share this product. Detach only the confirmed Sungrow
        // PDF so a corrected attachment is preserved if it was uploaded first.
        DB::connection('mongodb')->getDatabase()->selectCollection('products')->updateOne(
            [
                '_id' => new ObjectId('6a6300224ad0c3d7f602293d'),
                'datasheet.path' => 'products/datasheets/c612c2bd-7c44-4a07-8232-1306f514f60b.pdf',
            ],
            ['$unset' => ['datasheet' => '']],
        );
    }

    public function down(): void
    {
        // Leave the known incorrect PDF detached.
    }
};

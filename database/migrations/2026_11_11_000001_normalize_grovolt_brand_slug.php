<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\Regex;

return new class extends Migration
{
    public function up(): void
    {
        $brands = DB::connection('mongodb')->getDatabase()->selectCollection('brands');
        $scope = ['name' => 'Grovolt', 'slug' => 'Grovolt', 'is_active' => true, 'deleted_at' => null];
        $matches = $brands->find($scope, ['limit' => 2])->toArray();

        if (count($matches) === 0) {
            return;
        }

        $brandId = $matches[0]['_id'];
        if (count($matches) > 1 || $brands->findOne([
            '_id' => ['$ne' => $brandId],
            'slug' => new Regex('^grovolt$', 'i'),
            'deleted_at' => null,
        ])) {
            throw new RuntimeException('Grovolt slug repair found multiple brand records for the same public URL.');
        }

        $brands->updateOne([...$scope, '_id' => $brandId], ['$set' => ['slug' => 'grovolt']]);
    }

    public function down(): void
    {
        // Preserve the repaired public URL after rolling back application code.
    }
};

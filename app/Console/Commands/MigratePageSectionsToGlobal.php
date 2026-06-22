<?php

namespace App\Console\Commands;

use App\Models\PageSection;
use App\Models\PageSetting;
use Illuminate\Console\Command;

class MigratePageSectionsToGlobal extends Command
{
    protected $signature   = 'page-sections:migrate-to-global';
    protected $description = 'Migrate page sections to global';

    public function handle()
    {
        $this->info('=== Page Sections Debug ===');
        $this->newLine();

        // Total count
        $total = PageSection::count();
        $this->info("Total page sections: {$total}");

        // Count by location_id
        $this->newLine();
        $this->info('Sections by location_id:');
        $byLocation = PageSection::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => ['_id' => '$location_id', 'count' => ['$sum' => 1]]]
            ]);
        });
        foreach ($byLocation as $loc) {
            $locId = $loc->_id ?? 'NULL/GLOBAL';
            $this->line("  - {$locId}: {$loc->count}");
        }

        // Test global query
        $this->newLine();
        $globalCount = PageSection::where(function ($q) {
            $q->whereNull('location_id')
              ->orWhereRaw(['location_id' => ['$exists' => false]]);
        })->count();
        $this->info("Global sections (null or not exists): {$globalCount}");

        $this->newLine();
        $this->info('=== Done ===');

        return 0;
    }
}

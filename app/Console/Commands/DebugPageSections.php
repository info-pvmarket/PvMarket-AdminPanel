<?php

namespace App\Console\Commands;

use App\Models\PageSection;
use App\Models\PageSetting;
use App\Models\Market;
use App\Models\Country;
use App\Models\Location;
use Illuminate\Console\Command;
use MongoDB\BSON\ObjectId;

class DebugPageSections extends Command
{
    protected $signature   = 'page-sections:debug {--market= : Specific market ID to debug}';
    protected $description = 'Debug page sections market/country chain';

    public function handle()
    {
        $this->info('=== Page Sections Debug ===');
        $this->newLine();

        $specificMarketId = $this->option('market');

        if ($specificMarketId) {
            // Debug specific market
            $this->debugSpecificMarket($specificMarketId);
        } else {
            // Debug all markets
            $this->debugAllMarkets();
        }

        // Check all location_ids in PageSections
        $this->newLine();
        $this->info('=== All location_ids in PageSections ===');
        $locationIds = PageSection::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => ['_id' => '$location_id', 'count' => ['$sum' => 1]]]
            ]);
        });
        foreach ($locationIds as $loc) {
            $locId = $loc->_id ?? 'NULL/GLOBAL';
            if ($loc->_id) {
                // Try to find country with this _id
                $country = Country::find($loc->_id);
                $countryName = $country ? $country->name : 'NOT FOUND IN COUNTRIES';
                $this->line("  {$locId} ({$countryName}): {$loc->count} sections");
            } else {
                $this->line("  {$locId}: {$loc->count} sections");
            }
        }

        $this->newLine();
        $this->info('=== Done ===');

        return 0;
    }

    private function debugSpecificMarket(string $marketId)
    {
        $this->info("Debugging Market ID: {$marketId}");
        $this->newLine();

        $market = Market::find($marketId);
        if (!$market) {
            $this->error("Market not found with ID: {$marketId}");
            return;
        }

        $this->line("Market: {$market->name}");
        $this->line("Market Code: " . ($market->code ?? 'NULL'));
        $this->line("Market _id: {$market->_id}");
        $this->newLine();

        if (!$market->code) {
            $this->error("Market has no code - cannot lookup country");
            return;
        }

        $iso2 = strtoupper($market->code);
        $this->line("Looking up Country with iso2: {$iso2}");

        $country = Country::where('iso2', $iso2)->first();
        if (!$country) {
            $this->error("No Country found with iso2: {$iso2}");
            return;
        }

        $this->line("Country: {$country->name}");
        $this->line("Country _id: {$country->_id}");
        $this->line("Country iso2: {$country->iso2}");
        $this->newLine();

        $countryId = (string) $country->_id;
        $this->info("Expected location_id for this market: {$countryId}");
        $this->newLine();

        // Count sections with this country_id - try both string and ObjectId
        $countAsString = PageSection::where('location_id', $countryId)->count();
        $this->line("Sections with location_id = '{$countryId}' (string): {$countAsString}");

        $countAsObjectId = PageSection::where('location_id', new ObjectId($countryId))->count();
        $this->line("Sections with location_id = ObjectId('{$countryId}'): {$countAsObjectId}");

        // Show sample sections
        if ($countAsObjectId > 0) {
            $this->newLine();
            $this->info("Sample sections:");
            $samples = PageSection::where('location_id', new ObjectId($countryId))->limit(5)->get();
            foreach ($samples as $s) {
                $this->line("  - Page: {$s->page}, Section: {$s->section_key}, location_id: {$s->location_id}");
            }
        }

        // Check if there are sections with different location_id format
        $this->newLine();
        $this->info("Checking for sections with similar location_id patterns...");

        // Get all distinct location_ids
        $allLocationIds = PageSection::raw(function ($collection) {
            return $collection->distinct('location_id');
        });

        $this->line("All distinct location_ids in database:");
        foreach ($allLocationIds as $locId) {
            $locIdStr = $locId ?? 'NULL';
            $this->line("  - {$locIdStr}");
        }
    }

    private function debugAllMarkets()
    {
        $markets = Market::where('is_active', true)->get();
        $this->info("Active Markets: {$markets->count()}");

        foreach ($markets as $market) {
            $this->newLine();
            $this->line("Market: {$market->name} (code: {$market->code}, _id: {$market->_id})");

            // Find Country using iso2 (same logic as controller)
            if (!$market->code) {
                $this->warn("  ✗ Market has no code");
                continue;
            }

            $country = Country::where('iso2', strtoupper($market->code))->first();
            if ($country) {
                $countryId = (string) $country->_id;
                $this->line("  → Country: {$country->name} (_id: {$countryId})");

                // Count sections using country._id as location_id
                $sectionsCount = PageSection::where('location_id', $countryId)->count();
                $this->line("  → Sections with location_id = '{$countryId}': {$sectionsCount}");
            } else {
                $this->warn("  ✗ No Country found for iso2: " . strtoupper($market->code));
            }
        }
    }
}

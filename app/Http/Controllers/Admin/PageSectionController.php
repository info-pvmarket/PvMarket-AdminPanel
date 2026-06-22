<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageSection;
use App\Models\PageSetting;
use App\Models\Market;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use MongoDB\BSON\ObjectId;

class PageSectionController extends Controller
{
    private array $pages = [
        'home'        => 'Homepage',
        'about'       => 'About Us',
        'contact'     => 'Contact Us',
        'terms'       => 'Terms & Conditions',
        'delivery'    => 'Delivery & Return Policy',
        'disclaimer'  => 'Disclaimer',
        'privacy'     => 'Privacy Policy',
        'customer_support' => 'Customer Support',
        'faq'         => 'FAQ',
    ];

    /**
     * Check if market parameter is "global" (no location)
     */
    private function isGlobal(?string $marketId): bool
    {
        return $marketId === null || $marketId === 'global';
    }

    /**
     * Get country_id (used as location_id) from market
     * Market code -> Country (via iso2) -> country._id
     * Returns ObjectId for proper MongoDB querying
     */
    private function getCountryIdFromMarket(string $marketId): ?ObjectId
    {
        $market = Market::find($marketId);
        if (!$market || !$market->code) {
            return null;
        }

        $country = Country::where('iso2', strtoupper($market->code))->first();
        if (!$country) {
            return null;
        }

        return $country->_id instanceof ObjectId ? $country->_id : new ObjectId((string) $country->_id);
    }

    /**
     * Get market and country info for display
     */
    private function getMarketInfo(string $marketId): array
    {
        $market = Market::find($marketId);
        $country = null;
        $countryId = null;

        if ($market && $market->code) {
            $country = Country::where('iso2', strtoupper($market->code))->first();
            if ($country) {
                $countryId = $country->_id instanceof ObjectId ? $country->_id : new ObjectId((string) $country->_id);
            }
        }

        return [
            'market'    => $market,
            'country'   => $country,
            'countryId' => $countryId,  // ObjectId used as location_id for queries
        ];
    }

    /**
     * Apply global filter (location_id is null OR doesn't exist)
     */
    private function applyGlobalFilter($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('location_id')
              ->orWhereRaw(['location_id' => ['$exists' => false]]);
        });
    }

    /**
     * Show market grid (market selection)
     */
    public function index()
    {
        $markets = Market::where('is_active', true)->orderBy('name')->get();

        // Get country info for each market
        $marketData = [];
        foreach ($markets as $market) {
            $country = null;
            if ($market->code) {
                $country = Country::where('iso2', strtoupper($market->code))->first();
            }
            $marketData[$market->id] = [
                'market'  => $market,
                'country' => $country,
            ];
        }

        return view('admin.page-sections.index', [
            'markets'    => $markets,
            'marketData' => $marketData,
        ]);
    }

    /**
     * Show pages for a specific market (or global)
     */
    public function pages(string $marketId)
    {
        $isGlobal = $this->isGlobal($marketId);
        $markets = Market::where('is_active', true)->orderBy('name')->get();

        $market = null;
        $country = null;
        $countryId = null;

        if (!$isGlobal) {
            $info = $this->getMarketInfo($marketId);
            $market = $info['market'];
            $country = $info['country'];
            $countryId = $info['countryId'];
        }

        // Get country info for all markets (for copy dropdown)
        $marketData = [];
        foreach ($markets as $m) {
            $c = null;
            if ($m->code) {
                $c = Country::where('iso2', strtoupper($m->code))->first();
            }
            $marketData[$m->id] = [
                'market'  => $m,
                'country' => $c,
            ];
        }

        return view('admin.page-sections.pages', [
            'market'     => $market,
            'marketId'   => $marketId,
            'markets'    => $markets,
            'marketData' => $marketData,
            'pages'      => $this->pages,
            'isGlobal'   => $isGlobal,
            'country'    => $country,
            'countryId'  => $countryId,
        ]);
    }

    /**
     * Edit sections for a specific page and market (or global)
     */
    public function edit(string $marketId, string $page)
    {
        abort_unless(array_key_exists($page, $this->pages), 404);

        $isGlobal = $this->isGlobal($marketId);

        $market = null;
        $country = null;
        $countryId = null;

        if (!$isGlobal) {
            $info = $this->getMarketInfo($marketId);
            $market = $info['market'];
            $country = $info['country'];
            $countryId = $info['countryId'];
        }

        // Build query for sections - use countryId as location_id
        $sectionsQuery = PageSection::where('page', $page);
        if ($isGlobal) {
            $this->applyGlobalFilter($sectionsQuery);
        } else {
            $sectionsQuery->where('location_id', $countryId);
        }
        $sections = $sectionsQuery->orderBy('order')->get();

        // For settings
        if ($isGlobal) {
            $settingQuery = PageSetting::where('page', $page);
            $this->applyGlobalFilter($settingQuery);
            $setting = $settingQuery->first() ?? new PageSetting(['page' => $page, 'location_id' => null]);
        } else {
            $setting = PageSetting::firstOrNew([
                'page'        => $page,
                'location_id' => $countryId,
            ]);
        }

        return view('admin.page-sections.edit', [
            'market'    => $market,
            'marketId'  => $marketId,
            'page'      => $page,
            'pageLabel' => $this->pages[$page],
            'sections'  => $sections,
            'setting'   => $setting,
            'isGlobal'  => $isGlobal,
            'country'   => $country,
            'countryId' => $countryId,
        ]);
    }

    /**
     * Update sections for a specific page and market (or global)
     */
    public function update(Request $request, string $marketId, string $page)
    {
        $isGlobal = $this->isGlobal($marketId);
        $countryId = null;

        if (!$isGlobal) {
            $countryId = $this->getCountryIdFromMarket($marketId);
        }

        if ($request->has('sections')) {
            foreach ($request->sections as $sectionId => $data) {

                $section = PageSection::find($sectionId);
                if (!$section) continue;

                // ── Build extra, starting from what's already stored ──────────
                $extra = is_array($section->extra) ? $section->extra : [];

                if (isset($data['extra'])) {
                    $incoming = is_string($data['extra'])
                        ? json_decode($data['extra'], true)
                        : $data['extra'];

                    // ── Handle logo file uploads inside extra.items ───────────
                    if (isset($incoming['items']) && is_array($incoming['items'])) {
                        foreach ($incoming['items'] as $i => $item) {

                            // Check for a new uploaded file for this logo slot
                            $fileKey = "sections.{$sectionId}.extra.items.{$i}.logo_file";
                            if ($request->hasFile($fileKey)) {
                                // Delete old logo if one existed
                                $oldLogo = $extra['items'][$i]['logo'] ?? null;
                                if ($oldLogo) {
                                    Storage::disk('public')->delete($oldLogo);
                                }
                                // Store new file
                                $incoming['items'][$i]['logo'] = $request
                                    ->file($fileKey)
                                    ->store("logos/{$page}", 'public');
                            }

                            // Strip the transient logo_file key — we don't persist it
                            unset($incoming['items'][$i]['logo_file']);
                        }
                    }

                    $extra = array_merge($extra, $incoming);
                }

                // ── Build the rest of the update payload ─────────────────────
                $update = [
                    'title'       => $data['title']       ?? $section->title,
                    'subtitle'    => $data['subtitle']    ?? null,
                    'description' => $data['description'] ?? null,
                    'button_text' => $data['button_text'] ?? null,
                    'button_link' => $data['button_link'] ?? null,
                    'alt_tag'     => $data['alt_tag']     ?? null,
                    'is_active'   => isset($data['is_active']),
                    'extra'       => $extra,
                ];

                // ── Handle the single section-level image (non-logos) ─────────
                if ($request->hasFile("sections.{$sectionId}.image")) {
                    if ($section->image) {
                        Storage::disk('public')->delete($section->image);
                    }
                    $update['image'] = $request
                        ->file("sections.{$sectionId}.image")
                        ->store("sections/{$page}", 'public');
                }

                $section->update($update);
            }
        }

        // ── SEO settings ─────────────────────────────────────────────────────
        if ($isGlobal) {
            $settingQuery = PageSetting::where('page', $page);
            $this->applyGlobalFilter($settingQuery);
            $setting = $settingQuery->first();

            if ($setting) {
                $setting->update([
                    'seo_title'       => $request->seo_title,
                    'seo_description' => $request->seo_description,
                    'seo_keywords'    => $request->seo_keywords,
                    'is_published'    => $request->boolean('is_published'),
                ]);
            } else {
                PageSetting::create([
                    'page'            => $page,
                    'location_id'     => null,
                    'seo_title'       => $request->seo_title,
                    'seo_description' => $request->seo_description,
                    'seo_keywords'    => $request->seo_keywords,
                    'is_published'    => $request->boolean('is_published'),
                ]);
            }
        } else {
            PageSetting::updateOrCreate(
                ['page' => $page, 'location_id' => $countryId],
                [
                    'seo_title'       => $request->seo_title,
                    'seo_description' => $request->seo_description,
                    'seo_keywords'    => $request->seo_keywords,
                    'is_published'    => $request->boolean('is_published'),
                ]
            );
        }

        return redirect()->route('admin.page-sections.edit', [$marketId, $page])
                         ->with('success', $this->pages[$page] . ' updated successfully.');
    }

    /**
     * Copy page sections from another market (or global) to the current one
     * Uses country._id as location_id
     */
    public function copyFrom(Request $request, string $marketId)
    {
        $request->validate([
            'source_market_id' => 'required|string',
        ]);

        $isGlobal = $this->isGlobal($marketId);
        $targetCountryId = null;
        $targetName = 'Global';

        if (!$isGlobal) {
            $info = $this->getMarketInfo($marketId);
            $targetCountryId = $info['countryId'];
            $targetName = $info['country']->name ?? $info['market']->name ?? 'Unknown';
        }

        $sourceMarketId = $request->source_market_id;
        $sourceIsGlobal = $this->isGlobal($sourceMarketId);
        $sourceCountryId = null;
        $sourceName = 'Global';

        if (!$sourceIsGlobal) {
            $sourceInfo = $this->getMarketInfo($sourceMarketId);
            $sourceCountryId = $sourceInfo['countryId'];
            $sourceName = $sourceInfo['country']->name ?? $sourceInfo['market']->name ?? 'Unknown';
        }

        // Get all sections from source
        $sourceQuery = PageSection::query();
        if ($sourceIsGlobal) {
            $this->applyGlobalFilter($sourceQuery);
        } else {
            $sourceQuery->where('location_id', $sourceCountryId);
        }
        $sourceSections = $sourceQuery->get();

        if ($sourceSections->isEmpty()) {
            return redirect()->route('admin.page-sections.pages', $marketId)
                             ->with('error', 'No sections found in ' . $sourceName . ' to copy.');
        }

        // Delete existing sections for target (using country_id as location_id)
        if ($isGlobal) {
            $deleteQuery = PageSection::query();
            $this->applyGlobalFilter($deleteQuery);
            $deleteQuery->delete();
        } else {
            PageSection::where('location_id', $targetCountryId)->delete();
        }

        // Copy sections with country_id as location_id
        foreach ($sourceSections as $sourceSection) {
            $data = $sourceSection->toArray();
            unset($data['_id'], $data['id'], $data['created_at'], $data['updated_at']);
            $data['location_id'] = $isGlobal ? null : $targetCountryId;
            PageSection::create($data);
        }

        // Copy page settings as well
        $settingsQuery = PageSetting::query();
        if ($sourceIsGlobal) {
            $this->applyGlobalFilter($settingsQuery);
        } else {
            $settingsQuery->where('location_id', $sourceCountryId);
        }
        $sourceSettings = $settingsQuery->get();

        if ($isGlobal) {
            $deleteSettingsQuery = PageSetting::query();
            $this->applyGlobalFilter($deleteSettingsQuery);
            $deleteSettingsQuery->delete();
        } else {
            PageSetting::where('location_id', $targetCountryId)->delete();
        }

        foreach ($sourceSettings as $sourceSetting) {
            $data = $sourceSetting->toArray();
            unset($data['_id'], $data['id'], $data['created_at'], $data['updated_at']);
            $data['location_id'] = $isGlobal ? null : $targetCountryId;
            PageSetting::create($data);
        }

        $copiedCount = $sourceSections->count();

        return redirect()->route('admin.page-sections.pages', $marketId)
                         ->with('success', "Copied {$copiedCount} sections from {$sourceName} to {$targetName}.");
    }
}

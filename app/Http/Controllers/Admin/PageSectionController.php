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
use App\Rules\RichTextImages;

class PageSectionController extends Controller
{
    /**
     * Static pages that can be edited here.
     *
     * This screen manages body content only - SEO (meta title, OG, Twitter,
     * robots) for these same pages is edited under SEO Meta's "Static Pages"
     * group (App\Support\SeoStaticPages), which must list the same keys.
     * The keys also double as the /content-blocks `page` parameter, so they
     * must match what the storefront asks for.
     */
    private array $pages = [
        'home'             => 'Homepage',
        'about'            => 'About Us',
        'contact'          => 'Contact Us',
        'terms'            => 'Terms & Conditions',
        'delivery'         => 'Delivery & Return Policy',
        'disclaimer'       => 'Disclaimer',
        'privacy'          => 'Privacy Policy',
        'customer_support' => 'Customer Support',
        'faq'              => 'FAQ',
    ];

    /**
     * Check if market parameter is "global" (no location)
     */
    private function isGlobal(?string $marketId): bool
    {
        return $marketId === null || $marketId === 'global' || (!str_starts_with($marketId, 'region:') && strtolower((string)Market::find($marketId)?->code) === 'global');
    }

    /**
     * Get country_id (used as location_id) from market
     * Market code -> Country (via iso2) -> country._id
     * Returns ObjectId for proper MongoDB querying
     */
    private function getCountryIdFromMarket(string $marketId): ?ObjectId
    {
        $region = \App\Support\StorefrontScopes::region($marketId);
        if ($region) return null;
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
        $region = \App\Support\StorefrontScopes::region($marketId);
        if ($region) return ['market'=>(object)['id'=>$marketId, 'name'=>$region['name'], 'code'=>$region['code']], 'country'=>null, 'countryId'=>null];
        $market = Market::find($marketId);
        $country = null;
        $countryId = null;

        if ($market && $market->code) {
            $country = Country::where('iso2', strtoupper($market->code))->first();
            if ($country) {
                $countryId = $country->_id instanceof ObjectId ? $country->_id : new ObjectId((string) $country->_id);
            }
        }

        abort_unless($market && $countryId, 404);
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
        return $query->whereNull('region_code')->where(function ($q) {
            $q->whereNull('location_id')
              ->orWhereRaw(['location_id' => ['$exists' => false]]);
        });
    }

    private function applyScope($query, string $selection)
    {
        $region = \App\Support\StorefrontScopes::region($selection);
        if ($region) return $query->where('region_code', $region['code'])->whereNull('location_id');
        if ($this->isGlobal($selection)) return $this->applyGlobalFilter($query);
        $info = $this->getMarketInfo($selection);
        return $query->whereNull('region_code')->where('location_id', $info['countryId']);
    }

    private function deleteUnsharedAsset(string $path, PageSection $section): void
    {
        $shared = PageSection::where('_id', '!=', $section->getKey())->where(function ($query) use ($path) {
            $query->where('image', $path)->orWhere('extra.items.logo', $path);
        })->exists();
        if (!$shared) Storage::disk('public')->delete($path);
    }

    private function scopeIdentity(string $selection): array
    {
        $region = \App\Support\StorefrontScopes::region($selection);
        return ['region_code'=>$region['code'] ?? null, 'location_id'=>$region || $this->isGlobal($selection) ? null : $this->getMarketInfo($selection)['countryId']];
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
        $this->applyScope($sectionsQuery, $marketId);
        $sections = $sectionsQuery->orderBy('order')->get();

        $setting = $this->applyScope(PageSetting::where('page', $page), $marketId)->first()
            ?? new PageSetting(['page'=>$page] + $this->scopeIdentity($marketId));

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
        abort_unless(array_key_exists($page, $this->pages), 404);

        $request->validate(['sections.*.extra' => [new RichTextImages]]);

        $isGlobal = $this->isGlobal($marketId);
        $countryId = null;

        if (!$isGlobal) {
            $countryId = $this->getCountryIdFromMarket($marketId);
        }

        if ($request->has('sections')) {
            foreach ($request->sections as $sectionId => $data) {

                $section = $this->applyScope(PageSection::where('page', $page), $marketId)->where('_id', $sectionId)->first();
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
                                    $this->deleteUnsharedAsset($oldLogo, $section);
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
                        $this->deleteUnsharedAsset($section->image, $section);
                    }
                    $update['image'] = $request
                        ->file("sections.{$sectionId}.image")
                        ->store("sections/{$page}", 'public');
                }

                $section->update($update);
            }
        }

        $setting = $this->applyScope(PageSetting::where('page', $page), $marketId)->first();
        if ($setting) $setting->update(['is_published'=>$request->boolean('is_published')]);
        else PageSetting::create(['page'=>$page, 'is_published'=>$request->boolean('is_published')] + $this->scopeIdentity($marketId));

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

        $source = $request->source_market_id;
        $targetIdentity = $this->scopeIdentity($marketId);
        $sourceIdentity = $this->scopeIdentity($source);
        if ($sourceIdentity == $targetIdentity) return back()->with('error', 'Choose a different source.');
        $sections = $this->applyScope(PageSection::query(), $source)->get();
        if ($sections->isEmpty()) return back()->with('error', 'No source sections to copy.');
        $settings = $this->applyScope(PageSetting::query(), $source)->get();
        $this->applyScope(PageSection::query(), $marketId)->delete();
        $this->applyScope(PageSetting::query(), $marketId)->delete();
        foreach ([$sections, $settings] as $records) foreach ($records as $record) {
            $data = $record->toArray();
            unset($data['_id'], $data['id'], $data['created_at'], $data['updated_at']);
            $data = array_merge($data, $targetIdentity);
            $copy = $record instanceof PageSection ? new PageSection() : new PageSetting();
            $copy->forceFill($data)->save();
        }
        return redirect()->route('admin.page-sections.pages', $marketId)->with('success', 'Page sections and publishing settings copied.');
    }
}

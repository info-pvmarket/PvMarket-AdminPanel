<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\MarketDomain;
use App\Models\MarketSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use MongoDB\BSON\ObjectId;

class MarketController extends Controller
{
    // ─── INDEX ───────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $entries = (int) $request->get('entries', 10);
        $search  = $request->get('search');

        $query = Market::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $records = $query->latest()->paginate($entries)->withQueryString();

        // Load domains count for each market
        foreach ($records as $market) {
            $marketId = new ObjectId((string) $market->_id);
            $market->domains_count = MarketDomain::where('market_id', $marketId)->count();
        }

        return view('admin.setup.markets.index', [
            'records' => $records,
        ]);
    }

    // ─── CREATE ──────────────────────────────────────────────────────────────

    public function create()
    {
        return view('admin.setup.markets.create');
    }

    // ─── STORE ───────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'code'             => 'required|string|max:10|unique:mongodb.markets,code',
            'name'             => 'required|string|max:255',
            'default_currency' => 'required|string|max:10',
            'default_locale'   => 'required|string|max:10',
        ]);

        $market = Market::create([
            'code'             => strtolower($request->code),
            'name'             => $request->name,
            'default_currency' => strtoupper($request->default_currency),
            'default_locale'   => $request->default_locale,
            'is_active'        => $request->boolean('is_active', true),
        ]);

        // Create default settings
        $marketId = new ObjectId((string) $market->_id);
        MarketSettings::create([
            'market_id'            => $marketId,
            'site_name'            => $request->site_name ?? $request->name,
            'site_description'     => $request->site_description ?? '',
            'contact_email'        => $request->contact_email ?? '',
            'contact_phone'        => $request->contact_phone ?? '',
            'contact_address'      => $request->contact_address ?? '',
            'social_links'         => [],
            'gtm_container_id'     => $request->gtm_container_id ?? '',
            'google_analytics_id'  => $request->google_analytics_id ?? '',
            'available_currencies' => [$request->default_currency],
            'features'             => [
                'rfq_enabled'      => true,
                'checkout_enabled' => true,
                'bidding_enabled'  => true,
            ],
            'metadata_base'        => $request->metadata_base ?? '',
            'default_country_code' => $request->default_country_code ?? '',
            'filter_by_country'    => $request->boolean('filter_by_country'),
        ]);

        return redirect()->route('admin.setup.markets.index')
                         ->with('success', 'Market created successfully.');
    }

    // ─── EDIT ────────────────────────────────────────────────────────────────

    public function edit(string $id)
    {
        $record   = Market::findOrFail($id);
        $marketId = new ObjectId((string) $record->_id);
        $settings = MarketSettings::where('market_id', $marketId)->first();
        $domains  = MarketDomain::where('market_id', $marketId)->get();
        $countries = \App\Models\Country::orderBy('name')->get();

        return view('admin.setup.markets.edit', [
            'record'    => $record,
            'settings'  => $settings,
            'domains'   => $domains,
            'countries' => $countries,
        ]);
    }

    // ─── UPDATE ──────────────────────────────────────────────────────────────

    public function update(Request $request, string $id)
    {
        $request->validate([
            'code'             => 'required|string|max:10|unique:mongodb.markets,code,' . $id . ',_id',
            'name'             => 'required|string|max:255',
            'default_currency' => 'required|string|max:10',
            'default_locale'   => 'required|string|max:10',
        ]);

        $record = Market::findOrFail($id);

        $record->update([
            'code'             => strtolower($request->code),
            'name'             => $request->name,
            'default_currency' => strtoupper($request->default_currency),
            'default_locale'   => $request->default_locale,
            'is_active'        => $request->boolean('is_active'),
        ]);

        // Update or create settings (remove all scopes to ensure we find any existing record)
        $marketId = new ObjectId((string) $record->_id);
        $settings = MarketSettings::withoutGlobalScopes()
            ->where('market_id', $marketId)
            ->first();

        $settingsData = [
            'market_id'            => $marketId,
            'site_name'            => $request->site_name ?? $record->name,
            'site_description'     => $request->site_description ?? '',
            'contact_email'        => $request->contact_email ?? '',
            'contact_phone'        => $request->contact_phone ?? '',
            'contact_address'      => $request->contact_address ?? '',
            'gtm_container_id'     => $request->gtm_container_id ?? '',
            'google_analytics_id'  => $request->google_analytics_id ?? '',
            'metadata_base'        => $request->metadata_base ?? '',
            'social_links'         => [
                'facebook'  => $request->social_facebook ?? '',
                'twitter'   => $request->social_twitter ?? '',
                'linkedin'  => $request->social_linkedin ?? '',
                'instagram' => $request->social_instagram ?? '',
                'youtube'   => $request->social_youtube ?? '',
            ],
            'available_currencies' => array_filter(array_map('trim', explode(',', $request->available_currencies ?? ''))),
            'features'             => [
                'rfq_enabled'      => $request->boolean('feature_rfq'),
                'checkout_enabled' => $request->boolean('feature_checkout'),
                'bidding_enabled'  => $request->boolean('feature_bidding'),
            ],
            'default_country_code' => $request->default_country_code ?? '',
            'filter_by_country'    => $request->boolean('filter_by_country'),
        ];

        if ($settings) {
            // Restore if soft-deleted, then update
            if ($settings->deleted_at !== null) {
                $settings->restore();
            }
            $settings->update($settingsData);
        } else {
            MarketSettings::create($settingsData);
        }

        return redirect()->route('admin.setup.markets.index')
                         ->with('success', 'Market updated successfully.');
    }

    // ─── TOGGLE ──────────────────────────────────────────────────────────────

    public function toggle(string $id)
    {
        $record = Market::findOrFail($id);
        $record->update(['is_active' => !$record->is_active]);

        return redirect()->route('admin.setup.markets.index')
                         ->with('success', 'Market status updated.');
    }

    // ─── DESTROY ─────────────────────────────────────────────────────────────

    public function destroy(string $id)
    {
        $record = Market::findOrFail($id);
        $marketId = new ObjectId((string) $record->_id);

        // Delete related domains and settings
        MarketDomain::where('market_id', $marketId)->delete();
        MarketSettings::where('market_id', $marketId)->delete();

        $record->delete();

        return redirect()->route('admin.setup.markets.index')
                         ->with('success', 'Market deleted successfully.');
    }

    // ─── DOMAIN MANAGEMENT ───────────────────────────────────────────────────

    public function addDomain(Request $request, string $id)
    {
        $request->validate([
            'domain' => 'required|string|max:255|unique:mongodb.market_domains,domain',
        ]);

        $market = Market::findOrFail($id);
        $marketId = new ObjectId((string) $market->_id);

        MarketDomain::create([
            'market_id'  => $marketId,
            'domain'     => strtolower(trim($request->domain)),
            'is_primary' => $request->boolean('is_primary'),
        ]);

        return redirect()->route('admin.setup.markets.edit', $id)
                         ->with('success', 'Domain added successfully.');
    }

    public function removeDomain(string $marketId, string $domainId)
    {
        MarketDomain::findOrFail($domainId)->delete();

        return redirect()->route('admin.setup.markets.edit', $marketId)
                         ->with('success', 'Domain removed successfully.');
    }

    public function setPrimaryDomain(string $marketId, string $domainId)
    {
        $market = Market::findOrFail($marketId);
        $marketObjId = new ObjectId((string) $market->_id);

        // Remove primary from all domains of this market
        MarketDomain::where('market_id', $marketObjId)->update(['is_primary' => false]);

        // Set the selected domain as primary
        MarketDomain::findOrFail($domainId)->update(['is_primary' => true]);

        return redirect()->route('admin.setup.markets.edit', $marketId)
                         ->with('success', 'Primary domain updated.');
    }
}

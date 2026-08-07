<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currency;
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
        return view('admin.setup.markets.create', [
            'marketCountries' => $this->marketCountryOptions(),
        ]);
    }

    // ─── STORE ───────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->merge([
            'code' => strtolower(trim((string) $request->code)),
        ]);

        $request->validate([
            'code'          => $this->marketCodeRules(),
            'name'          => 'required|string|max:255',
            'calendly_link' => 'nullable|url|max:2048',
        ]);

        $defaultCurrency = Currency::where('code', 'USD')->exists()
            ? 'USD'
            : (Currency::orderBy('code')->value('code') ?? 'USD');

        $market = Market::create([
            'code'             => $request->code,
            'name'             => $request->name,
            'default_currency' => $defaultCurrency,
            'default_locale'   => 'en-US',
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
            'calendly_link'        => $request->calendly_link ?? '',
            'social_links'         => [],
            'gtm_container_id'     => $request->gtm_container_id ?? '',
            'google_analytics_id'  => $request->google_analytics_id ?? '',
            'features'             => [
                'rfq_enabled'      => true,
                'checkout_enabled' => true,
                'bidding_enabled'  => true,
            ],
            'metadata_base'        => $request->metadata_base ?? '',
            'default_country_code' => strtoupper($request->code),
            'filter_by_country'    => true,
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
        $countries = Country::orderBy('name')->get();

        return view('admin.setup.markets.edit', [
            'record'          => $record,
            'settings'        => $settings,
            'countries'       => $countries,
            'marketCountries' => $this->marketCountryOptions(),
        ]);
    }

    // ─── UPDATE ──────────────────────────────────────────────────────────────

    public function update(Request $request, string $id)
    {
        $record = Market::findOrFail($id);

        $request->merge([
            'code' => strtolower(trim((string) $request->code)),
        ]);

        $request->validate([
            'code'          => $this->marketCodeRules($id, strtolower((string) $record->code) === 'global'),
            'name'          => 'required|string|max:255',
            'calendly_link' => 'nullable|url|max:2048',
        ]);

        $record->update([
            'code' => $request->code,
            'name' => $request->name,
        ]);

        // Update or create settings (remove all scopes to ensure we find any existing record)
        $marketId = new ObjectId((string) $record->_id);
        $settings = MarketSettings::withoutGlobalScopes()
            ->where('market_id', $marketId)
            ->first();

        $settingsData = [
            'market_id'            => $marketId,
            'contact_email'        => $request->contact_email ?? '',
            'contact_phone'        => $request->contact_phone ?? '',
            'contact_address'      => $request->contact_address ?? '',
            'calendly_link'        => $request->calendly_link ?? '',
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
            MarketSettings::create($settingsData + [
                'site_name'           => $record->name,
                'site_description'    => '',
                'social_links'        => [],
                'gtm_container_id'    => '',
                'google_analytics_id' => '',
                'metadata_base'       => '',
                'features'            => [
                    'rfq_enabled'      => true,
                    'checkout_enabled' => true,
                    'bidding_enabled'  => true,
                ],
            ]);
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

        // Delete directly from MongoDB so this action can never be converted into
        // a soft delete by the models' SoftDeletes trait.
        MarketDomain::raw(
            fn ($collection) => $collection->deleteMany(['market_id' => $marketId])
        );
        MarketSettings::raw(
            fn ($collection) => $collection->deleteMany(['market_id' => $marketId])
        );

        $result = Market::raw(
            fn ($collection) => $collection->deleteOne(['_id' => $marketId])
        );

        abort_unless($result->getDeletedCount() === 1, 500, 'Unable to permanently delete market.');

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

    private function marketCountryOptions()
    {
        return Country::orderBy('name')
            ->get()
            ->map(function (Country $country) {
                $code = strtoupper(trim((string) ($country->iso2 ?: $country->code)));

                return [
                    'code' => $code,
                    'name' => $country->name,
                ];
            })
            ->filter(fn (array $country) => preg_match('/^[A-Z]{2}$/', $country['code']) === 1)
            ->unique('code')
            ->values();
    }

    private function marketCodeRules(?string $marketId = null, bool $allowGlobal = false): array
    {
        $uniqueRule = $marketId
            ? 'unique:mongodb.markets,code,' . $marketId . ',_id'
            : 'unique:mongodb.markets,code';

        return [
            'required',
            'string',
            'max:10',
            function (string $attribute, mixed $value, \Closure $fail) use ($allowGlobal) {
                if ($allowGlobal && $value === 'global') {
                    return;
                }

                if (!preg_match('/^[a-z]{2}$/', (string) $value)) {
                    $fail('Please select a valid country for the market code.');

                    return;
                }

                $countryCode = strtoupper((string) $value);
                $countryExists = Country::where(function ($query) use ($countryCode) {
                    $query->where('iso2', $countryCode)
                        ->orWhere('code', $countryCode);
                })->exists();

                if (!$countryExists) {
                    $fail('The selected country is not available.');
                }
            },
            $uniqueRule,
        ];
    }
}

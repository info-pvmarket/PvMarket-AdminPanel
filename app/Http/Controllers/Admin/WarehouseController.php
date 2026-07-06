<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\TranslationService;
use App\Traits\FiltersAssignedUsers;

class WarehouseController extends Controller
{
    use FiltersAssignedUsers;

    public function __construct(protected TranslationService $translator) {}
    // ── Index ─────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Warehouse::query();

        // Filter by assigned users
        $this->filterByAssignedUsers($query, 'user_id');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $warehouses = $query->orderBy('created_at', 'desc')
                            ->paginate($request->get('entries', 10));

        return view('admin.warehouses.warehouses', [
            'mode'       => 'index',
            'warehouses' => $warehouses,
        ]);
    }

    // ── Create ────────────────────────────────────────
    public function create()
    {
        $countries = Country::orderBy('name', 'asc')->get(['_id', 'name']);

        return view('admin.warehouses.warehouses', [
            'mode'      => 'create',
            'record'    => null,
            'countries' => $countries,
        ]);
    }

    // ── Store ─────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'warehouse_name' => 'required|string|max:300',
            'country'        => 'required|string',
            'zip_code'                  => 'required|string|max:20',
            'street'                    => 'required|string|max:300',
            'apartment_suite'           => 'nullable|string|max:300',
            'city'                      => 'required|string|max:100',
            'warehouse_email'           => 'required|email|max:255',
            'contact_name'              => 'required|string|max:150',
            'contact_mobile'            => 'required|string|max:30',
            'ddp_deliverable_countries' => 'nullable|array',
            'ddp_deliverable_countries.*' => 'string',
        ]);

        // Resolve country ObjectId and name
        $countryModel = \App\Models\Country::find($request->country);

        $data = [
            'user_id'                   => new \MongoDB\BSON\ObjectId(Auth::id()),
            'warehouse_name'            => $request->warehouse_name,
            'country'                   => $request->country
                                            ? new \MongoDB\BSON\ObjectId($request->country)
                                            : null,
            'country_name'              => $countryModel?->name ?? '',
            'zip_code'                  => $request->zip_code,
            'street'                    => $request->street,
            'apartment_suite'           => $request->apartment_suite ?? '',
            'city'                      => $request->city,
            'warehouse_email'           => $request->warehouse_email,
            'contact_name'              => $request->contact_name,
            'contact_mobile'            => $request->contact_mobile,
            'ddp_deliverable_countries' => collect($request->ddp_deliverable_countries ?? [])
                                            ->map(fn($id) => new \MongoDB\BSON\ObjectId($id))
                                            ->toArray(),
            'is_active'                 => true,
            'is_primary'                => false,
            'is_paid'                   => false,
            'payment_status'            => 'pending',
            'created_by'                => new \MongoDB\BSON\ObjectId(Auth::id()),
            'updated_by'                => new \MongoDB\BSON\ObjectId(Auth::id()),
        ];

        $data = $this->attachTranslations($data, new Warehouse());
        Warehouse::create($data);

        return redirect()->route('admin.warehouses.index')
                         ->with('success', 'Warehouse created successfully.');
    }

    // ── Edit ──────────────────────────────────────────
    public function edit($id)
    {
        $record    = Warehouse::findOrFail($id);
        $countries = Country::orderBy('name', 'asc')->get(['_id', 'name']);

        return view('admin.warehouses.warehouses', [
            'mode'      => 'edit',
            'record'    => $record,
            'countries' => $countries,
        ]);
    }

    // ── Update ────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $request->validate([
            'warehouse_name'              => 'required|string|max:300',
            'country'                     => 'required|string',
            'zip_code'                    => 'required|string|max:20',
            'street'                      => 'required|string|max:300',
            'apartment_suite'             => 'nullable|string|max:300',
            'city'                        => 'required|string|max:100',
            'warehouse_email'             => 'required|email|max:255',
            'contact_name'                => 'required|string|max:150',
            'contact_mobile'              => 'required|string|max:30',
            'ddp_deliverable_countries'   => 'nullable|array',
            'ddp_deliverable_countries.*' => 'string',
        ]);

        $warehouse = Warehouse::findOrFail($id);

        $countryModel = \App\Models\Country::find($request->country);

        $data = [
            'warehouse_name'            => $request->warehouse_name,
            'country'                   => $request->country
                                            ? new \MongoDB\BSON\ObjectId($request->country)
                                            : null,
            'country_name'              => $countryModel?->name ?? '',
            'zip_code'                  => $request->zip_code,
            'street'                    => $request->street,
            'apartment_suite'           => $request->apartment_suite ?? '',
            'city'                      => $request->city,
            'warehouse_email'           => $request->warehouse_email,
            'contact_name'              => $request->contact_name,
            'contact_mobile'            => $request->contact_mobile,
            'ddp_deliverable_countries' => collect($request->ddp_deliverable_countries ?? [])
                                            ->map(fn($id) => new \MongoDB\BSON\ObjectId($id))
                                            ->toArray(),
            'updated_by'                => new \MongoDB\BSON\ObjectId(Auth::id()),
        ];

        $data = $this->attachTranslations($data, $warehouse);
        $warehouse->update($data);

        return redirect()->route('admin.warehouses.index')
                         ->with('success', 'Warehouse updated.');
    }

    // ── Mark As Paid ──────────────────────────────────
    public function markAsPaid($id)
    {
        Warehouse::findOrFail($id)->update([
            'is_paid'        => true,
            'payment_status' => 'paid',
            'updated_by'     => Auth::user()->name,
        ]);

        return redirect()->route('admin.warehouses.index')
                         ->with('success', 'Warehouse marked as paid.');
    }

    // ── Toggle Active Status ───────────────────────────
    public function toggleStatus($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->update([
            'is_active'  => !$warehouse->is_active,
            'updated_by' => new \MongoDB\BSON\ObjectId(Auth::id()),
        ]);

        return redirect()->route('admin.warehouses.index')
                         ->with('success', 'Status updated.');
    }

    // ── Destroy ───────────────────────────────────────
    public function destroy($id)
    {
        Warehouse::findOrFail($id)->delete();

        return redirect()->route('admin.warehouses.index')
                         ->with('success', 'Warehouse deleted.');
    }
    private function attachTranslations(array $data, $modelInstance): array
    {
        $languages    = array_keys(config('languages.available'));
        $translatable = $modelInstance->translatable ?? [];

        foreach ($languages as $locale) {
            if ($locale === 'en') continue;

            $translated = [];
            foreach ($translatable as $field) {
                if (!empty($data[$field])) {
                    $translated[$field] = $this->translator->translateText(
                        $data[$field], $locale, 'en'
                    );
                }
            }

            if (!empty($translated)) {
                $data[$locale] = $translated;
            }
        }

        return $data;
    }
}

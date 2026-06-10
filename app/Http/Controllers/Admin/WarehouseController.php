<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\TranslationService;

class WarehouseController extends Controller
{
    public function __construct(protected TranslationService $translator) {}
    // ── Index ─────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Warehouse::query();

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
            'warehouse_name'            => 'required|string|max:300',
            'country'                   => 'required|string',
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

        $data = [
            'user_id'                   => (string) Auth::id(),
            'warehouse_name'            => $request->name,
            'country'                   => $request->country,
            'zip_code'                  => $request->zip_code,
            'street'                    => $request->street,
            'apartment_suite'           => $request->apartment_suite,
            'city'                      => $request->city,
            'warehouse_email'           => $request->warehouse_email,
            'contact_name'              => $request->contact_name,
            'contact_mobile'            => $request->contact_mobile,
            'ddp_deliverable_countries' => $request->ddp_deliverable_countries ?? [],
            'is_paid'                   => false,
            'is_active'                 => true,
            'updated_by'                => Auth::user()->name,
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

        $data = [
            'warehouse_name'            => $request->name,
            'country'                   => $request->country,
            'zip_code'                  => $request->zip_code,
            'street'                    => $request->street,
            'apartment_suite'           => $request->apartment_suite,
            'city'                      => $request->city,
            'warehouse_email'           => $request->warehouse_email,
            'contact_name'              => $request->contact_name,
            'contact_mobile'            => $request->contact_mobile,
            'ddp_deliverable_countries' => $request->ddp_deliverable_countries ?? [],
            'updated_by'                => Auth::user()->name,
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
            'is_paid'    => true,
            'updated_by' => Auth::user()->name,
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
            'updated_by' => Auth::user()->name,
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
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\TranslationService;

class OfferController extends Controller
{
    public function __construct(protected TranslationService $translator) {}
    // ── Index ─────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Offer::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('product_name',   'like', "%{$s}%")
                  ->orWhere('warehouse_name', 'like', "%{$s}%")
                  ->orWhere('unique_id',      'like', "%{$s}%");
            });
        }

        $offers = $query->orderBy('created_at', 'desc')
                        ->paginate($request->get('entries', 10));

        return view('admin.offers.offers', [
            'mode'   => 'index',
            'offers' => $offers,
        ]);
    }

    // ── Create ────────────────────────────────────────
    public function create()
    {
        $products   = Product::orderBy('product_name')->get(['_id', 'product_name']);
        $warehouses = Warehouse::orderBy('name')->get(['_id', 'name']);

        return view('admin.offers.offers', [
            'mode'       => 'create',
            'record'     => null,
            'products'   => $products,
            'warehouses' => $warehouses,
        ]);
    }

    // ── Store ─────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|string',
            'warehouse_id' => 'required|string',
        ]);

        $product   = Product::findOrFail($request->product_id);
        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        $data = [
            'unique_id'      => $this->generateUniqueId(),
            'product_id'     => $request->product_id,
            'product_name'   => $product->product_name,
            'warehouse_id'   => $request->warehouse_id,
            'warehouse_name' => $warehouse->warehouse_name,
            'payment_status' => 'pending',
            'is_active'      => true,
            'updated_by'     => Auth::user()->name,
        ];

        $data = $this->attachTranslations($data, new Offer());
        Offer::create($data);

        return redirect()->route('admin.offers.index')
                         ->with('success', 'Offer created successfully.');
    }

    // ── Edit ──────────────────────────────────────────
    public function edit($id)
    {
        $record     = Offer::findOrFail($id);
        $products   = Product::orderBy('product_name')->get(['_id', 'product_name']);
        $warehouses = Warehouse::orderBy('name')->get(['_id', 'name']);

        return view('admin.offers.offers', [
            'mode'       => 'edit',
            'record'     => $record,
            'products'   => $products,
            'warehouses' => $warehouses,
        ]);
    }

    // ── Update ────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $offer = Offer::findOrFail($id);

        $request->validate([
            'product_id'   => 'required|string',
            'warehouse_id' => 'required|string',
        ]);

        $product   = Product::findOrFail($request->product_id);
        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        $data = [
            'product_id'     => $request->product_id,
            'product_name'   => $product->product_name,
            'warehouse_id'   => $request->warehouse_id,
            'warehouse_name' => $warehouse->warehouse_name,
            'updated_by'     => Auth::user()->name,
        ];

        $data = $this->attachTranslations($data, $offer);
        $offer->update($data);

        return redirect()->route('admin.offers.index')
                         ->with('success', 'Offer updated.');
    }

    // ── Mark As Paid ──────────────────────────────────
    public function markAsPaid($id)
    {
        Offer::findOrFail($id)->update([
            'payment_status' => 'paid',
            'updated_by'     => Auth::user()->name,
        ]);

        return redirect()->route('admin.offers.index')
                         ->with('success', 'Offer marked as paid.');
    }

    // ── Toggle active status ──────────────────────────
    public function toggleStatus($id)
    {
        $offer = Offer::findOrFail($id);
        $offer->update([
            'is_active'  => !$offer->is_active,
            'updated_by' => Auth::user()->name,
        ]);

        return redirect()->route('admin.offers.index')
                         ->with('success', 'Status updated.');
    }

    // ── Destroy ───────────────────────────────────────
    public function destroy($id)
    {
        Offer::findOrFail($id)->delete();
        return redirect()->route('admin.offers.index')
                         ->with('success', 'Offer deleted.');
    }

    // ── Helper: generate a short alphanumeric unique ID ──
    private function generateUniqueId(): string
    {
        do {
            $id = Str::random(6);
        } while (Offer::where('unique_id', $id)->exists());

        return $id;
    }
    private function attachTranslations(array $data, $modelInstance): array
    {
        $languages    = array_keys(config('languages.available'));
        $translatable = $modelInstance->translatable ?? [];

        foreach ($languages as $locale) {
            if ($locale === 'en') continue;

            $translated = [];

            foreach ($translatable as $field) {
                $original = $data[$field] ?? null;

                if (empty($original) || !is_string($original)) continue;
                if (strlen(trim($original)) < 2) continue;

                try {
                    $result = $this->translator->translateText($original, $locale, 'en');
                    if ($result) {
                        $translated[$field] = $result;
                    }
                } catch (\Exception $e) {
                    Log::error("OfferController attachTranslations [{$locale}][{$field}]: " . $e->getMessage());
                }
            }

            if (!empty($translated)) {
                $data[$locale] = $translated;
            }
        }

        return $data;
    }
}
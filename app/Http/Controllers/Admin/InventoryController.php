<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\ProductListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MongoDB\BSON\ObjectId;
use App\Models\StockAlert;
use App\traits\HasTranslations;
use App\Services\TranslationService;
use App\Traits\FiltersAssignedUsers;

class InventoryController extends Controller
{
    use FiltersAssignedUsers;

    public function __construct(protected TranslationService $translator) {}
    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $userId = Auth::id();
        $filter = $request->get('filter', 'all'); // all | low_stock | out_of_stock
        $search = $request->get('search', '');

        $query = ProductListing::query()->with(['warehouse']);

        // Filter by assigned users
        $this->filterByAssignedUsers($query, 'user_id');

        if ($search) {
            $query->where('sku_code', 'like', "%{$search}%");
        }

        $listings = $query->get();

        // Enrich each listing with live stock + alert data
        $listings = $listings->map(function ($listing) use ($userId) {
    $id = (string) $listing->_id;

    $listing->current_stock = InventoryTransaction::currentStock($id);
    $listing->stock_unit    = $listing->stock_unit ?? 'pieces';

    if ($listing->warehouse) {
        $listing->warehouse_display_name =
            $listing->warehouse->warehouse_name ??
            $listing->warehouse->name ??
            'N/A';
    } else {
        $listing->warehouse_display_name = 'N/A';
    }

$alert = StockAlert::getAlert($id, (string) $userId);
$listing->alert_threshold     = $alert?->threshold ?? null;
$listing->alert_email_enabled = $alert?->notification_enabled ?? false;
$listing->alert_record_id     = $alert ? (string) $alert->_id : null;

            return $listing;
        });

        // Apply stock filters
        if ($filter === 'low_stock') {
            $listings = $listings->filter(fn($l) =>
                $l->alert_threshold !== null &&
                $l->current_stock > 0 &&
                $l->current_stock <= $l->alert_threshold
            );
        } elseif ($filter === 'out_of_stock') {
            $listings = $listings->filter(fn($l) => $l->current_stock === 0);
        }

        if ($request->ajax()) {
    $payload = $listings->values()->map(fn($l) => [
        '_id'                    => (string) $l->_id,
        'sku_code'               => $l->sku_code,
        'is_active'              => (bool) $l->is_active,
        'current_stock'          => $l->current_stock,
        'stock_unit'             => $l->stock_unit,
        'warehouse_display_name' => $l->warehouse_display_name,
        'alert_threshold'        => $l->alert_threshold,
        'alert_email_enabled'    => (bool) $l->alert_email_enabled,
        'alert_record_id'        => $l->alert_record_id,
    ]);
    return response()->json(['listings' => $payload]);
}

        $allListings = $listings->values();
        $totalListings   = $allListings->count();
        $lowStockCount   = $allListings->filter(fn($l) => $l->alert_threshold !== null && $l->current_stock > 0 && $l->current_stock <= $l->alert_threshold)->count();
        $outOfStockCount = $allListings->filter(fn($l) => $l->current_stock === 0)->count();
        $perPage  = 15;
        $page     = request()->get('page', 1);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $allListings->forPage($page, $perPage),
            $allListings->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.inventory.index', compact('filter', 'search', 'totalListings', 'lowStockCount', 'outOfStockCount') + ['listings' => $paginated]);
    }

    // ── Adjust Stock ──────────────────────────────────────────────────────────

    public function adjustStock(Request $request, string $listingId)
    {
        $request->validate([
            'type'     => 'required|in:add,reduce',
            'quantity' => 'required|integer|min:1',
            'notes'    => 'nullable|string|max:500',
        ]);

        $listing = ProductListing::where('_id', new ObjectId($listingId))
                         ->where('user_id', new ObjectId(Auth::id()))
                                 ->firstOrFail();

        $transactionType = $request->type === 'add' ? 'stock_add' : 'stock_reduce';
        $quantity        = (int) $request->quantity;

        $current = InventoryTransaction::currentStock($listingId);

        if ($request->type === 'reduce') {
            if ($quantity > $current) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot reduce by {$quantity}. Current stock is only {$current}.",
                ], 422);
            }
        }

        $txData = [
    'listing_id'        => new ObjectId((string) $listing->_id),
    'product_id'        => new ObjectId((string) $listing->product_id),
    'warehouse_id'      => $listing->warehouse_id ? new ObjectId((string) $listing->warehouse_id) : null,
    'user_id'           => new ObjectId(Auth::id()),
    'created_by'        => new ObjectId(Auth::id()),
    'transaction_type'  => $transactionType,
    'quantity'          => $quantity,

    // ── New fields ──────────────────────────────────
    'quantity_before'   => $current,             // stock before this transaction
    'quantity_after'    => $request->type === 'add' ? $current + $quantity : $current - $quantity,
    'quantity_change'   => $quantity,
    'type'              => $request->type === 'add' ? 'addition' : 'deduction',
    'reason'            => $request->notes,      // mirrors notes field
    // ────────────────────────────────────────────────

    'reference_type'    => 'listing',
    'reference_id'      => new ObjectId((string) $listing->_id),
    'notes'             => $request->notes,
];

$txData = $this->attachTranslations($txData, new InventoryTransaction());

InventoryTransaction::create($txData);

        $newStock = InventoryTransaction::currentStock($listingId);

        return response()->json([
            'success'   => true,
            'message'   => 'Stock updated successfully.',
            'new_stock' => $newStock,
        ]);
    }

    // ── History ───────────────────────────────────────────────────────────────

    public function history(string $listingId)
    {
        $listing = ProductListing::where('_id', new ObjectId($listingId))
                         ->where('user_id', new ObjectId(Auth::id()))
                                 ->firstOrFail();

        $transactions = InventoryTransaction::forListing($listingId)
            ->stockMovements()               // excludes alert_settings records
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($tx) => [
                'id'               => (string) $tx->_id,
                'transaction_type' => $tx->transaction_type,
                'label'            => $tx->transaction_label,
                'quantity'         => $tx->quantity,
                'is_addition'      => $tx->is_addition,
                'notes'            => $tx->notes,
                'created_at'       => $tx->created_at?->format('d M Y, H:i'),
            ]);

        return response()->json([
            'success'       => true,
            'sku'           => $listing->sku_code,
            'current_stock' => InventoryTransaction::currentStock($listingId),
            'stock_unit'    => $listing->stock_unit ?? 'pieces',
            'transactions'  => $transactions,
        ]);
    }

    // ── Alert Settings ────────────────────────────────────────────────────────

public function getAlert(string $listingId)
{
    $listing = ProductListing::where('_id', new ObjectId($listingId))
                     ->where('user_id', new ObjectId(Auth::id()))
                             ->firstOrFail();

    $alert = StockAlert::getAlert($listingId, (string) Auth::id());

    return response()->json([
        'success'       => true,
        'sku'           => $listing->sku_code,
        'current_stock' => InventoryTransaction::currentStock($listingId),
        'stock_unit'    => $listing->stock_unit ?? 'pieces',
        'threshold'     => $alert?->threshold,
        'email_enabled' => $alert?->notification_enabled ?? false,
        'alert_id'      => $alert ? (string) $alert->_id : null,
    ]);
}

public function saveAlert(Request $request, string $listingId)
{
    $request->validate([
        'threshold'     => 'required|integer|min:0',
        'email_enabled' => 'boolean',
    ]);

    $listing = ProductListing::where('_id', new ObjectId($listingId))
                             ->where('user_id', new ObjectId(Auth::id()))
                             ->firstOrFail();

    // Update existing or create new
    $alert = StockAlert::where('listing_id', new ObjectId($listingId))
                       ->where('user_id',    new ObjectId(Auth::id()))
                       ->first();

    $data = [
        'listing_id'           => new ObjectId((string) $listing->_id),
        'user_id'              => new ObjectId(Auth::id()),
        'threshold'            => (int) $request->threshold,
        'notification_enabled' => (bool) ($request->email_enabled ?? false),
        'is_active'            => true,
    ];

    if ($alert) {
        $alert->update($data);
    } else {
        $alert = StockAlert::create($data);
    }

    return response()->json([
        'success'  => true,
        'message'  => 'Alert saved.',
        'alert_id' => (string) $alert->_id,
    ]);
}

public function removeAlert(string $listingId)
{
    StockAlert::where('listing_id', new ObjectId($listingId))
              ->where('user_id',    new ObjectId(Auth::id()))
              ->delete();

    return response()->json(['success' => true, 'message' => 'Alert removed.']);
}
// ── Global Alert ──────────────────────────────────────────────────────────

public function saveGlobalAlert(Request $request)
{
    $request->validate([
        'thresholds'            => 'required|array',
        'thresholds.pieces'     => 'nullable|integer|min:0',
        'thresholds.pallets'    => 'nullable|integer|min:0',
        'thresholds.containers' => 'nullable|integer|min:0',
        'email_enabled'         => 'boolean',
    ]);

    $userId       = Auth::id();
    $thresholds   = $request->thresholds;
    $emailEnabled = (bool) ($request->email_enabled ?? false);

    // ✅ Match how index() fetches listings — no user_id filter
    $listings = ProductListing::query()->get();

    $updated = 0;

    foreach ($listings as $listing) {
        $unit = $listing->stock_unit ?? 'pieces';

        if (!isset($thresholds[$unit]) || $thresholds[$unit] === '' || $thresholds[$unit] === null) {
            continue;
        }

        $threshold = (int) $thresholds[$unit];
        $listingId = (string) $listing->_id;

        $alert = StockAlert::where('listing_id', new ObjectId($listingId))
                           ->where('user_id',    new ObjectId($userId))
                           ->first();

        $data = [
            'listing_id'           => new ObjectId($listingId),
            'user_id'              => new ObjectId($userId),
            'threshold'            => $threshold,
            'notification_enabled' => $emailEnabled,
            'is_active'            => true,
        ];

        if ($alert) {
            $alert->update($data);
        } else {
            StockAlert::create($data);
        }

        $updated++;
    }

    return response()->json([
        'success' => true,
        'message' => "Global alert applied to {$updated} listing(s).",
        'updated' => $updated,
    ]);
}
private function attachTranslations(array $data, $modelInstance): array
{
    $languages    = array_keys(config('languages.available'));
    $translatable = $modelInstance->translatable ?? [];

    foreach ($languages as $locale) {
        if ($locale === 'en') continue;

        $existing   = $modelInstance->exists ? ($modelInstance->{$locale} ?? []) : [];
        $translated = is_array($existing) ? $existing : [];

        foreach ($translatable as $field) {
            if (!empty($translated[$field])) continue;

            $original = $data[$field] ?? null;
            if (empty($original) || !is_string($original) || is_numeric($original)) continue;
            if (strlen(trim(strip_tags($original))) < 2) continue;

            try {
                $result = $this->translator->translateText($original, $locale, 'en');
                if ($result && $result !== $original) {
                    $translated[$field] = $result;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error(
                    "attachTranslations [{$locale}][{$field}]: " . $e->getMessage()
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
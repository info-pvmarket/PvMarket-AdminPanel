<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\ProductListingImage;
use App\Models\InventoryTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\TranslationService;
use MongoDB\BSON\ObjectId;
use App\Traits\FiltersAssignedUsers;

class SalesController extends Controller
{
    use FiltersAssignedUsers;

    public function __construct(protected TranslationService $translator) {}
    public function index(Request $request)
{
    // $query = Order::where('is_active', true);

    $query = Order::query();

     $this->filterByAssignedUsers($query, 'created_by');

    if ($request->filled('user_id')) {
        $query->where('user_id', new \MongoDB\BSON\ObjectId($request->user_id));
    }

    if ($request->filled('product_id')) {
        $productListingIds = ProductListing::where('product_id', new ObjectId((string) $request->product_id))
            ->get(['_id'])
            ->pluck('_id')
            ->filter()
            ->map(fn($id) => new ObjectId((string) $id))
            ->values()
            ->all();

        if (empty($productListingIds)) {
            $query->whereRaw(['_id' => ['$exists' => false]]);
        } else {
            $query->whereIn('offer_id', $productListingIds);
        }
    }

    if ($request->filled('search')) {
        $query->where('unique_id', 'like', '%' . $request->search . '%');
    }

    if ($request->filled('order_status') && $request->order_status !== '') {
        $query->where('order_status', (int)$request->order_status);
    }

    if ($request->filled('payment_method') && $request->payment_method !== '') {
        $query->where('payment_method', (int)$request->payment_method);
    }

    // ── CHANGED: paginate instead of get ──
    $perPage = (int) $request->input('per_page', 10);
    $orders  = $query->orderBy('created_at', 'desc')->paginate($perPage);

    // Products dropdown
    $products = Product::orderBy('product_name')->get(['_id', 'product_name']);

    // User info
    $user = null;
    if ($request->filled('user_id')) {
        $user = User::find($request->user_id);
    }
    

    // ── CHANGED: map the current page items only, then set them back ──
    $listingIds = collect($orders->items())
        ->pluck('offer_id')
        ->filter()
        ->unique()
        ->map(fn($id) => (string) $id)
        ->values();

    $listingsMap = $listingIds->isEmpty()
        ? collect()
        : ProductListing::whereIn('_id', $listingIds)
            ->get()
            ->keyBy(fn($listing) => (string) $listing->_id);

    $productIds = $listingsMap->pluck('product_id')
        ->filter()
        ->unique()
        ->map(fn($id) => (string) $id)
        ->values();

    $productsMap = $productIds->isEmpty()
        ? collect()
        : Product::whereIn('_id', $productIds)
            ->get()
            ->keyBy(fn($product) => (string) $product->_id);

    $buyerIds = collect($orders->items())
        ->pluck('user_id')
        ->filter()
        ->unique()
        ->map(fn($id) => (string) $id)
        ->values();

    $sellerIds = $listingsMap->pluck('user_id')
        ->filter()
        ->unique()
        ->map(fn($id) => (string) $id)
        ->values();

    $userIds = $buyerIds->merge($sellerIds)->unique()->values();

    $usersMap = $userIds->isEmpty()
        ? collect()
        : User::whereIn('_id', $userIds)
            ->get()
            ->keyBy(fn($user) => (string) $user->_id);

    $listingObjectIds = $listingIds
        ->map(fn($id) => new ObjectId((string) $id))
        ->all();

    $listingImagesMap = empty($listingObjectIds)
        ? collect()
        : ProductListingImage::whereIn('product_listing_id', $listingObjectIds)
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn($image) => (string) $image->product_listing_id);

    // Mutate the paginator's items in place
    $orders->getCollection()->transform(function ($order) use ($listingsMap, $productsMap, $usersMap, $listingImagesMap) {
        $listing = $listingsMap[(string) $order->offer_id] ?? null;
        $product = $listing ? ($productsMap[(string) $listing->product_id] ?? null) : null;
        $buyer = $usersMap[(string) $order->user_id] ?? null;
        $seller = $listing ? ($usersMap[(string) $listing->user_id] ?? null) : null;
        $listingImages = $listing ? ($listingImagesMap[(string) $listing->_id] ?? collect()) : collect();

        $order->product_listing = $listing;
        $order->product_info = $product;
        $order->buyer_info = $buyer;
        $order->seller_info = $seller;
        $order->listing_image = $listingImages->first();
        $order->product_name_display = $product?->product_name ?? '—';
        return $order;
    });

    // ── CHANGED: append all current query params so filters survive page clicks ──
    $orders->appends($request->query());

    // dd($orders, $products, $user);

    return view('admin.sales.index', compact('orders', 'products', 'user'));
}

    // ── Mark partial payment as verified ─────────────────────────
    public function markPaymentVerified(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['payment_verified' => 1]);

        return response()->json(['success' => true, 'message' => 'Payment marked as verified.']);
    }

    // ── Update order status ───────────────────────────────────────
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['order_status' => 'required|integer|min:0|max:4']);

        $newStatus = (int) $request->order_status;

        // Fetch the order
        $order = Order::findOrFail($id);
        $oldStatus = (int) $order->order_status;

        // Get the listing
        $listing = ProductListing::find($order->offer_id);

        // CASE 1: Transitioning TO confirmed (status 1) - reduce stock
        if ($newStatus === 1 && $oldStatus !== 1 && $listing) {
            $quantity = (int) $order->total_qty;
            $currentStock = InventoryTransaction::currentStock((string) $listing->_id);

            // Create inventory transaction for sale
            InventoryTransaction::create([
                'listing_id'       => new ObjectId((string) $listing->_id),
                'product_id'       => $listing->product_id,
                'warehouse_id'     => $listing->warehouse_id,
                'user_id'          => $listing->user_id,
                'transaction_type' => 'sale',
                'quantity'         => $quantity,
                'quantity_before'  => $currentStock,
                'quantity_after'   => $currentStock - $quantity,
                'quantity_change'  => $quantity,
                'type'             => 'deduction',
                'reason'           => 'Sale confirmed - Order #' . $order->unique_id,
                'reference_type'   => 'order',
                'reference_id'     => new ObjectId($id),
                'notes'            => 'Sale confirmed - Order #' . $order->unique_id,
                'created_by'       => new ObjectId(auth()->id()),
            ]);

            // Reduce total_quantity in product listing
            $newQuantity = max(0, (int) $listing->total_quantity - $quantity);
            $listing->update(['total_quantity' => $newQuantity]);
        }

        // CASE 2: Transitioning TO cancelled (status 4) FROM confirmed (status 1) - restore stock
        if ($newStatus === 4 && $oldStatus === 1 && $listing) {
            $quantity = (int) $order->total_qty;
            $currentStock = InventoryTransaction::currentStock((string) $listing->_id);

            // Create inventory transaction for sale cancellation (add stock back)
            InventoryTransaction::create([
                'listing_id'       => new ObjectId((string) $listing->_id),
                'product_id'       => $listing->product_id,
                'warehouse_id'     => $listing->warehouse_id,
                'user_id'          => $listing->user_id,
                'transaction_type' => 'sale_cancelled',
                'quantity'         => $quantity,
                'quantity_before'  => $currentStock,
                'quantity_after'   => $currentStock + $quantity,
                'quantity_change'  => $quantity,
                'type'             => 'addition',
                'reason'           => 'Sale cancelled - Order #' . $order->unique_id,
                'reference_type'   => 'order',
                'reference_id'     => new ObjectId($id),
                'notes'            => 'Sale cancelled - Order #' . $order->unique_id,
                'created_by'       => new ObjectId(auth()->id()),
            ]);

            // Add back to total_quantity in product listing
            $newQuantity = (int) $listing->total_quantity + $quantity;
            $listing->update(['total_quantity' => $newQuantity]);
        }

        // Update order status
        $order->update([
            'order_status' => $newStatus,
            'updated_by'   => auth()->id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function viewProof($id)
{
    $order = Order::findOrFail($id);

    if (empty($order->transaction_upload)) {
        abort(404, 'No proof uploaded for this order.');
    }

    // Try storage path first
    $relativePath = $order->transaction_upload;
    $fullPath     = storage_path('app/public/' . $relativePath);

    if (file_exists($fullPath)) {
        $mimeType = mime_content_type($fullPath);
        return response()->file($fullPath, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
        ]);
    }

    // Try public path
    $publicPath = public_path($relativePath);
    if (file_exists($publicPath)) {
        return response()->file($publicPath);
    }

    abort(404, 'Proof file not found on server.');
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
            // Skip if already translated
            if (!empty($translated[$field])) continue;

            $original = $data[$field] ?? null;

            // Must be a non-empty string, not numeric, not an object
            if (empty($original) || !is_string($original) || is_numeric($original)) continue;

            // Skip if it's just whitespace or HTML tags with no real content
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

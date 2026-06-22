<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\TranslationService;

class SalesController extends Controller
{
    public function __construct(protected TranslationService $translator) {}
    public function index(Request $request)
{
    $query = Order::where('is_active', true);

    if ($request->filled('user_id')) {
        $query->where('user_id', new \MongoDB\BSON\ObjectId($request->user_id));
    }

    if ($request->filled('product_id')) {
        $query->where('offer_id', new \MongoDB\BSON\ObjectId($request->product_id));
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
    $productIds = collect($orders->items())
        ->pluck('offer_id')
        ->filter()
        ->unique()
        ->values()
        ->map(fn($id) => new \MongoDB\BSON\ObjectId((string)$id))
        ->toArray();

    $products_map = Product::whereIn('_id', $productIds)
        ->get()
        ->keyBy(fn($p) => (string)$p->_id);

    // Mutate the paginator's items in place
    $orders->getCollection()->transform(function ($order) use ($products_map) {
        $product = $products_map[(string)$order->offer_id] ?? null;
        $order->product_info         = $product;
        $order->product_name_display = $product?->product_name ?? '—';
        return $order;
    });

    // ── CHANGED: append all current query params so filters survive page clicks ──
    $orders->appends($request->query());

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

        $order = Order::findOrFail($id);
        $order->update([
    'order_status' => $request->order_status,
    'updated_by'   => auth()->id(),  // mutator handles ObjectId cast automatically
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
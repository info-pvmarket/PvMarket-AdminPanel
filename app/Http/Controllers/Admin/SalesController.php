<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\User;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::where('is_active', true);

        // Filter by user (buyer)
        if ($request->filled('user_id')) {
            $query->where('user_id', new \MongoDB\BSON\ObjectId($request->user_id));
        }

        // Filter by product — needs to resolve through product_listing → product_id
        if ($request->filled('product_id')) {
            $productOid = new \MongoDB\BSON\ObjectId($request->product_id);
            $listingIds = ProductListing::where('product_id', $productOid)
                ->pluck('_id')
                ->map(fn($id) => new \MongoDB\BSON\ObjectId((string)$id))
                ->toArray();

            $query->whereIn('offer_id', $listingIds);
        }

        if ($request->filled('search')) {
            $query->where('unique_id', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('order_status') && $request->order_status !== '') {
            $statusVal = $request->order_status;
            if (is_numeric($statusVal)) {
                $statusVal = Order::statusLabel((int)$statusVal);
            }
            $query->where('order_status', $statusVal);
        }

        if ($request->filled('payment_method') && $request->payment_method !== '') {
            $query->where('payment_method', (int)$request->payment_method);
        }

        // Paginate
        $perPage = (int) $request->input('per_page', 10);
        $orders  = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Products dropdown (for the filter)
        $products = Product::orderBy('product_name')->get(['_id', 'product_name']);

        // User info (if filtering by user)
        $user = null;
        if ($request->filled('user_id')) {
            $user = User::find($request->user_id);
        }

        // ── Eager-load listing → product data for the current page ──
        $offerIds = collect($orders->items())
            ->pluck('offer_id')
            ->filter()
            ->unique()
            ->values()
            ->map(fn($id) => new \MongoDB\BSON\ObjectId((string)$id))
            ->toArray();

        // Load product listings
        $listings = ProductListing::whereIn('_id', $offerIds)->get()->keyBy(fn($l) => (string)$l->_id);

        // Collect unique product_ids from listings to load products
        $productIds = $listings->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->map(fn($id) => new \MongoDB\BSON\ObjectId((string)$id))
            ->toArray();

        $productsMap = Product::whereIn('_id', $productIds)->get()->keyBy(fn($p) => (string)$p->_id);

        // Collect unique user_ids (buyers) and company_ids (sellers)
        $userIds = collect($orders->items())
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values()
            ->map(fn($id) => new \MongoDB\BSON\ObjectId((string)$id))
            ->toArray();

        $usersMap = User::whereIn('_id', $userIds)->get()->keyBy(fn($u) => (string)$u->_id);

        // Load seller companies — seller = listing->user_id
        $sellerUserIds = $listings->pluck('user_id')
            ->filter()
            ->unique()
            ->values()
            ->map(fn($id) => new \MongoDB\BSON\ObjectId((string)$id))
            ->toArray();

        $sellerUsersMap = User::whereIn('_id', $sellerUserIds)->get()->keyBy(fn($u) => (string)$u->_id);

        // Mutate the paginator's items — attach product_info, buyer_name, seller_name
        $orders->getCollection()->transform(function ($order) use ($listings, $productsMap, $usersMap, $sellerUsersMap) {
            $listing = $listings[(string)$order->offer_id] ?? null;
            $product = null;

            if ($listing) {
                $product = $productsMap[(string)$listing->product_id] ?? null;
            }

            $order->product_info         = $product;
            $order->product_name_display = $product?->product_name ?? '—';

            // Get listing images
            $order->listing_images = $listing?->images ?? [];

            // Buyer info
            $buyer = $usersMap[(string)$order->user_id] ?? null;
            $order->buyer_name = $buyer?->name ?? '—';

            // Seller info (from listing)
            $sellerUser = $listing ? ($sellerUsersMap[(string)$listing->user_id] ?? null) : null;
            $order->seller_name = $sellerUser?->name ?? '—';

            return $order;
        });

        // Append all current query params so filters survive page clicks
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
        try {
            $request->validate([
                'order_status' => 'required|string|in:Pending under payment verification,Confirmed,Shipped,Delivered,Cancelled,Pending',
            ]);

            $order = Order::findOrFail($id);
            $newStatusLabel = $request->order_status;
            $newStatusInt = Order::statusToInt($newStatusLabel);

            $order->update([
                'order_status' => $newStatusLabel,
                'updated_by'   => auth()->id(),
                'updated_at'   => now(),
            ]);

            return response()->json([
                'success'      => true,
                'order_status' => $newStatusInt,
                'status_label' => $newStatusLabel,
                'status_color' => Order::statusColorClass($newStatusInt),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Save note + record status snapshot in status_notes history ──
    public function updateNote(Request $request, $id)
    {
        try {
            $request->validate([
                'status_note'  => 'required|string|max:1000',
                'order_status' => 'nullable|string|in:Pending under payment verification,Confirmed,Shipped,Delivered,Cancelled,Pending',
            ]);

            $order = Order::findOrFail($id);
            $note  = trim((string) $request->status_note);
            $statusLabel = $request->filled('order_status')
                ? $request->order_status
                : ($order->order_status ?? 'Pending');
            $statusInt = Order::statusToInt($statusLabel);

            // Append to history (oldest → newest)
            $history = $order->status_notes ?? [];
            if (!is_array($history)) $history = [];

            $entry = [
                'order_status' => $statusLabel,
                'note'         => $note,
                'created_at'   => now()->toIso8601String(),
            ];
            if (auth()->id()) {
                $entry['created_by'] = (string) auth()->id();
            }
            $history[] = $entry;

            $order->update([
                'order_status' => $statusLabel,
                'status_note'  => $note,
                'status_notes' => $history,
                'updated_by'   => auth()->id(),
                'updated_at'   => now(),
            ]);

            return response()->json([
                'success'      => true,
                'order_status' => $statusInt,
                'status_label' => $statusLabel,
                'status_color' => Order::statusColorClass($statusInt),
                'status_notes' => $history,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function formatStatusNotesForJson(Order $order): array
    {
        return collect($order->getStatusNotesHistory())
            ->map(function ($entry) {
                $rawStatus = $entry['order_status'] ?? 0;
                $statusInt = is_string($rawStatus) ? Order::statusToInt($rawStatus) : (int) $rawStatus;
                $statusLabel = is_string($rawStatus) ? $rawStatus : Order::statusLabel($statusInt);
                $at     = $entry['created_at'] ?? null;
                if ($at instanceof \DateTimeInterface) {
                    $at = $at->format('c');
                }

                return [
                    'order_status' => $statusInt,
                    'status_label' => $statusLabel,
                    'status_short' => Order::statusShortLabel($statusInt),
                    'status_color' => Order::statusColorClass($statusInt),
                    'note'         => (string) ($entry['note'] ?? ''),
                    'created_at'   => $at,
                ];
            })
            ->values()
            ->all();
    }

    public function viewProof($id)
    {
        $order = Order::findOrFail($id);

        if (empty($order->transaction_upload)) {
            abort(404, 'No proof uploaded for this order.');
        }

        $upload = $order->transaction_upload;

        // If it's a full URL (e.g. R2/S3), redirect to it
        if (str_starts_with($upload, 'http://') || str_starts_with($upload, 'https://')) {
            return redirect($upload);
        }

        // Try storage path first
        $fullPath = storage_path('app/public/' . $upload);
        if (file_exists($fullPath)) {
            $mimeType = mime_content_type($fullPath);
            return response()->file($fullPath, [
                'Content-Type'        => $mimeType,
                'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
            ]);
        }

        // Try public path
        $publicPath = public_path($upload);
        if (file_exists($publicPath)) {
            return response()->file($publicPath);
        }

        abort(404, 'Proof file not found on server.');
    }
}
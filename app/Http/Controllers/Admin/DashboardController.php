<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\User;
use App\Models\Order;
use App\Models\BidRequest;
use App\Models\RfqRequest;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with statistics and latest items.
     */
    public function index()
    {
        // ── Counts ──────────────────────────────────────────────────────
        $listingsCount = ProductListing::whereNull('deleted_at')->count();
        $realTimePricingsCount = ProductListing::whereNull('deleted_at')
            ->where('real_time_price', true)
            ->count();
        $productsCount = Product::whereNull('deleted_at')->count();
        $totalStocks = ProductListing::whereNull('deleted_at')->sum('total_quantity');
        $salesCount = Order::count();

        // Pending counts
        $pendingProductsCount = Product::where('verification_status', 'pending')
            ->whereNull('deleted_at')
            ->count();

        $pendingListingsCount = ProductListing::where('verification_status', 'pending')
            ->whereNull('deleted_at')
            ->count();

        // ── Latest 5 Users ──────────────────────────────────────────────
        $latestUsers = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['_id', 'name', 'email', 'created_at', 'is_active']);

        // ── Latest 5 Listings ───────────────────────────────────────────
        $latestListings = ProductListing::whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->with(['product:_id,product_name', 'user:_id,name'])
            ->get(['_id', 'product_id', 'user_id', 'sku_code', 'created_at', 'verification_status', 'total_quantity']);

        // ── Latest 5 Products ───────────────────────────────────────────
        $latestProducts = Product::whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['_id', 'product_name', 'brand_name', 'category_name', 'created_at', 'verification_status']);

        // ── Latest 5 Inventory Transactions ─────────────────────────────
        $latestInventory = InventoryTransaction::orderBy('created_at', 'desc')
            ->limit(5)
            ->with(['listing:_id,sku_code', 'user:_id,name'])
            ->get(['_id', 'listing_id', 'user_id', 'transaction_type', 'quantity', 'created_at']);

        // ── Latest 5 Bid/Fair Price Requests ────────────────────────────
        $latestBidRequests = BidRequest::orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['_id', 'unique_id', 'product_name', 'company_name', 'request_type', 'status', 'created_at']);

        // ── Latest 5 RFQ Requests ───────────────────────────────────────
        $latestRfqRequests = RfqRequest::orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['_id', 'rfq_no', 'company_name', 'contact_person', 'total_items', 'status', 'created_at']);

        return view('admin.dashboard', [
            'listingsCount' => $listingsCount,
            'realTimePricingsCount' => $realTimePricingsCount,
            'productsCount' => $productsCount,
            'totalStocks' => $totalStocks,
            'salesCount' => $salesCount,
            'pendingProductsCount' => $pendingProductsCount,
            'pendingListingsCount' => $pendingListingsCount,
            'latestUsers' => $latestUsers,
            'latestListings' => $latestListings,
            'latestProducts' => $latestProducts,
            'latestInventory' => $latestInventory,
            'latestBidRequests' => $latestBidRequests,
            'latestRfqRequests' => $latestRfqRequests,
        ]);
    }
}

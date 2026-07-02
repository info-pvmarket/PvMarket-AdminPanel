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
use App\Models\Role;
use App\Traits\FiltersAssignedUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MongoDB\BSON\ObjectId;

class DashboardController extends Controller
{
    use FiltersAssignedUsers;

    /**
     * Display the admin dashboard with statistics and latest items.
     */
    public function index()
    {
        $admin = Auth::user();
        $isSuperAdmin = $admin && $admin->isSuperAdmin();

        // Get assigned user IDs for non-super admins
        $assignedUserIds = [];
        $assignedUserObjectIds = [];
        if (!$isSuperAdmin && $admin) {
            $assignedUserIds = $admin->getAssignedUserIds();
            $assignedUserIds = array_filter($assignedUserIds, fn($id) => !empty($id));
            $assignedUserObjectIds = array_map(fn($id) => new ObjectId($id), $assignedUserIds);
        }

        // ── Counts ──────────────────────────────────────────────────────
        $listingsQuery = ProductListing::whereNull('deleted_at');
        $this->filterByAssignedUsers($listingsQuery, 'user_id');
        $listingsCount = $listingsQuery->count();

        $realTimePricingsQuery = ProductListing::whereNull('deleted_at')->where('real_time_price', true);
        $this->filterByAssignedUsers($realTimePricingsQuery, 'user_id');
        $realTimePricingsCount = $realTimePricingsQuery->count();

        $productsQuery = Product::whereNull('deleted_at');
        $this->filterByAssignedUsers($productsQuery, 'created_by');
        $productsCount = $productsQuery->count();

        $totalStocksQuery = ProductListing::whereNull('deleted_at');
        $this->filterByAssignedUsers($totalStocksQuery, 'user_id');
        $totalStocks = $totalStocksQuery->sum('total_quantity');

        $salesQuery = Order::query();
        if (!$isSuperAdmin && !empty($assignedUserObjectIds)) {
            $salesQuery->whereIn('user_id', $assignedUserObjectIds);
        } elseif (!$isSuperAdmin && empty($assignedUserObjectIds)) {
            $salesQuery->whereRaw(['_id' => ['$exists' => false]]);
        }
        $salesCount = $salesQuery->count();

        // Pending counts
        $pendingProductsQuery = Product::where('verification_status', 'pending')->whereNull('deleted_at');
        $this->filterByAssignedUsers($pendingProductsQuery, 'created_by');
        $pendingProductsCount = $pendingProductsQuery->count();

        $pendingListingsQuery = ProductListing::where('verification_status', 'pending')->whereNull('deleted_at');
        $this->filterByAssignedUsers($pendingListingsQuery, 'user_id');
        $pendingListingsCount = $pendingListingsQuery->count();

        // ── Latest 5 Users ──────────────────────────────────────────────
        // Get admin role IDs to exclude admin users from this list
        $roleIds = Role::whereIn('slug', ['super-admin', 'admin'])->pluck('id');
        $roleObjectIds = $roleIds->map(fn($id) => new ObjectId((string) $id))->toArray();

        $usersQuery = User::where(function ($q) use ($roleObjectIds) {
            $q->whereNull('role_id')
              ->orWhereNotIn('role_id', $roleObjectIds);
        });

        // Filter by assigned users for non-super admins
        if (!$isSuperAdmin && $admin) {
            $usersQuery->where('assigned_admin_id', new ObjectId($admin->_id));
        }

        $latestUsers = $usersQuery->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['_id', 'name', 'email', 'created_at', 'is_active']);

        // ── Latest 5 Listings ───────────────────────────────────────────
        $latestListingsQuery = ProductListing::whereNull('deleted_at');
        $this->filterByAssignedUsers($latestListingsQuery, 'user_id');
        $latestListings = $latestListingsQuery->orderBy('created_at', 'desc')
            ->limit(5)
            ->with(['product:_id,product_name', 'user:_id,name'])
            ->get(['_id', 'product_id', 'user_id', 'sku_code', 'created_at', 'verification_status', 'total_quantity']);

        // ── Latest 5 Products ───────────────────────────────────────────
        $latestProductsQuery = Product::whereNull('deleted_at');
        $this->filterByAssignedUsers($latestProductsQuery, 'created_by');
        $latestProducts = $latestProductsQuery->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['_id', 'product_name', 'brand_name', 'category_name', 'created_at', 'verification_status']);

        // ── Latest 5 Inventory Transactions ─────────────────────────────
        $latestInventoryQuery = InventoryTransaction::query();
        $this->filterByAssignedUsers($latestInventoryQuery, 'user_id');
        $latestInventory = $latestInventoryQuery->orderBy('created_at', 'desc')
            ->limit(5)
            ->with(['listing:_id,sku_code', 'user:_id,name'])
            ->get(['_id', 'listing_id', 'user_id', 'transaction_type', 'quantity', 'created_at']);

        // ── Latest 5 Bid/Fair Price Requests ────────────────────────────
        $latestBidRequestsQuery = BidRequest::query();
        $this->filterByAssignedAdmin($latestBidRequestsQuery);
        $latestBidRequests = $latestBidRequestsQuery->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['_id', 'unique_id', 'product_name', 'company_name', 'request_type', 'status', 'created_at']);

        // ── Latest 5 RFQ Requests ───────────────────────────────────────
        $latestRfqRequestsQuery = RfqRequest::query();
        $this->filterByAssignedAdmin($latestRfqRequestsQuery);
        $latestRfqRequests = $latestRfqRequestsQuery->orderBy('created_at', 'desc')
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

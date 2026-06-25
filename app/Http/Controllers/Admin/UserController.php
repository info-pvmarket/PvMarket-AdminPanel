<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\ProductListing;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductListingImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\TranslationService;
use App\Traits\FiltersAssignedUsers;

class UserController extends Controller
{
    use FiltersAssignedUsers;

    public function __construct(protected TranslationService $translator) {}

    public function index(Request $request)
    {
        // Get admin role IDs to exclude admin users from this list
        $roleIds = \App\Models\Role::whereIn('slug', ['super-admin', 'admin'])->pluck('id');
        $roleObjectIds = $roleIds->map(fn($id) => new \MongoDB\BSON\ObjectId((string) $id))->toArray();

        // Start with users who are not admins (no role_id or role_id not in admin roles)
        $query = User::where(function ($q) use ($roleObjectIds) {
            $q->whereNull('role_id')
              ->orWhereNotIn('role_id', $roleObjectIds);
        });

        // Super Admin sees all users, regular admin sees only assigned users
        if (!Auth::user()->isSuperAdmin()) {
            $query->where('assigned_admin_id', new \MongoDB\BSON\ObjectId(Auth::id()));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name',  'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // dd($query->toMql()); // Debugging: Output the SQL query for inspection
        $users = $query->orderBy('created_at', 'desc')
                       ->paginate($request->get('entries', 10));

        $admins = $this->getAdminsForAssignment();

        return view('admin.users.index', compact('users', 'admins'));
    }

    public function edit(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $userId = new \MongoDB\BSON\ObjectId($id);

        // ── Documents ─────────────────────────────────────────────────
        $documents = $user->documents ?? [];

        // ── Listings (ProductListings by this user) ───────────────────
        $listingsQuery = ProductListing::where('user_id', $userId);

        // Apply filters
        $listingFilter = $request->get('listing_filter', 'all');
        $listingStatus = $request->get('listing_status', 'all');
        $listingPayment = $request->get('listing_payment', 'all');
        $listingRealtime = $request->get('listing_realtime', 'all');

        if ($listingFilter !== 'all') {
            $listingsQuery->where('verification_status', $listingFilter);
        }
        if ($listingStatus === 'active') {
            $listingsQuery->where('is_active', true);
        } elseif ($listingStatus === 'on_hold') {
            $listingsQuery->where('is_active', false);
        }
        if ($listingPayment === 'paid') {
            $listingsQuery->where('is_paid', true);
        } elseif ($listingPayment === 'unpaid') {
            $listingsQuery->where('is_paid', false);
        }
        if ($listingRealtime === 'enabled') {
            $listingsQuery->where('real_time_price', true);
        } elseif ($listingRealtime === 'disabled') {
            $listingsQuery->where(function($q) {
                $q->where('real_time_price', false)->orWhereNull('real_time_price');
            });
        }

        $listings = $listingsQuery->orderBy('created_at', 'desc')->get();

        // Prepare listings data maps
        $listingProductIds = $listings->pluck('product_id')->filter()->unique()
            ->map(fn($id) => (string)$id)->values();
        $listingWarehouseIds = $listings->pluck('warehouse_id')->filter()->unique()
            ->map(fn($id) => (string)$id)->values();

        $listingProductsMap = Product::whereIn('_id', $listingProductIds)->get()
            ->keyBy(fn($p) => (string)$p->_id);
        $listingWarehousesMap = Warehouse::whereIn('_id', $listingWarehouseIds)->get()
            ->keyBy(fn($w) => (string)$w->_id);

        $listingIds = $listings->pluck('_id')
            ->map(fn($id) => new \MongoDB\BSON\ObjectId((string)$id))
            ->toArray();
        $listingImagesMap = ProductListingImage::whereIn('product_listing_id', $listingIds)
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn($img) => (string)$img->product_listing_id);

        // ── Purchases (Orders where this user is the buyer) ───────────
        $purchases = Order::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get related listings for purchases
        $purchaseListingIds = $purchases->pluck('offer_id')->filter()->unique()
            ->map(fn($id) => (string)$id)->values();
        $purchaseListingsMap = ProductListing::whereIn('_id', $purchaseListingIds)->get()
            ->keyBy(fn($l) => (string)$l->_id);

        // Get products and warehouses for purchase listings
        $purchaseProductIds = $purchaseListingsMap->pluck('product_id')->filter()->unique()
            ->map(fn($id) => (string)$id)->values();
        $purchaseWarehouseIds = $purchaseListingsMap->pluck('warehouse_id')->filter()->unique()
            ->map(fn($id) => (string)$id)->values();

        $purchaseProductsMap = Product::whereIn('_id', $purchaseProductIds)->get()
            ->keyBy(fn($p) => (string)$p->_id);
        $purchaseWarehousesMap = Warehouse::whereIn('_id', $purchaseWarehouseIds)->get()
            ->keyBy(fn($w) => (string)$w->_id);

        // Get seller users for purchases
        $sellerUserIds = $purchaseListingsMap->pluck('user_id')->filter()->unique()
            ->map(fn($id) => (string)$id)->values();
        $sellerUsersMap = User::whereIn('_id', $sellerUserIds)->get()
            ->keyBy(fn($u) => (string)$u->_id);

        // ── Sales (Orders where this user's listings were purchased) ──
        $userListingIds = $listings->pluck('_id')
            ->map(fn($id) => new \MongoDB\BSON\ObjectId((string)$id))
            ->toArray();

        $sales = Order::whereIn('offer_id', $userListingIds)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get buyer info for sales
        $buyerUserIds = $sales->pluck('user_id')->filter()->unique()
            ->map(fn($id) => (string)$id)->values();
        $buyerUsersMap = User::whereIn('_id', $buyerUserIds)->get()
            ->keyBy(fn($u) => (string)$u->_id);

        // Sales listings map (reuse from listings)
        $salesListingsMap = $listings->keyBy(fn($l) => (string)$l->_id);

        return view('admin.users.edit', compact(
            'user',
            'documents',
            'listings',
            'listingProductsMap',
            'listingWarehousesMap',
            'listingImagesMap',
            'listingFilter',
            'listingStatus',
            'listingPayment',
            'listingRealtime',
            'purchases',
            'purchaseListingsMap',
            'purchaseProductsMap',
            'purchaseWarehousesMap',
            'sellerUsersMap',
            'sales',
            'salesListingsMap',
            'buyerUsersMap'
        ));
    }

    public function updateBasic(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:mongodb.users,email,' . $id . ',_id',
            'mobile' => 'nullable|string|max:30',
        ]);

        $data = [
            'name'   => $request->name,
            'email'  => $request->email,
            'mobile' => $request->mobile ?? null,
        ];

        $data = $this->attachTranslations($data, $user);
        $user->update($data);

        return redirect()->route('admin.users.edit', ['id' => $id, 'active_tab' => 'basic'])
                         ->with('success', 'Basic details updated.');
    }

    public function updateCompany(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'company_name' => 'nullable|string|max:255',
            'vat_id'       => 'nullable|string|max:100',
        ]);

        $user->update([
            'company_name'         => $request->company_name,
            'vat_id'               => $request->vat_id,
            'enable_editable'      => $request->boolean('enable_editable'),
            'allow_document_upload'=> $request->boolean('allow_document_upload'),
            'company_verified'     => $request->boolean('company_verified'),
            'show_verified_batch'  => $request->boolean('show_verified_batch'),
        ]);

        return redirect()->route('admin.users.edit', ['id' => $id, 'active_tab' => 'company'])
                         ->with('success', 'Company details updated.');
    }

    public function toggleCompanyVerified(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update(['company_verified' => !($user->company_verified ?? false)]);

        // Preserve the current active tab from request
        $activeTab = $request->input('active_tab', 'basic');

        return redirect()->route('admin.users.edit', ['id' => $id, 'active_tab' => $activeTab])
                         ->with('success', 'Company verification status updated.');
    }

    public function export(Request $request)
{
    $query = User::query();
 
    // ── Apply the same filters as index ──────────────────────
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name',  'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }
 
    
 
    $users = $query->orderBy('created_at', 'desc')->get();
 
    // ── Build CSV in memory ───────────────────────────────────
    $filename = 'users_export_' . now()->format('Y-m-d_His') . '.csv';
 
    $headers = [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        'Pragma'              => 'no-cache',
        'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
        'Expires'             => '0',
    ];
 
    $callback = function () use ($users) {
        $handle = fopen('php://output', 'w');
 
        // UTF-8 BOM so Excel opens it correctly
        fputs($handle, "\xEF\xBB\xBF");
 
        // ── Column headers ────────────────────────────────────
        fputcsv($handle, [
    'S.No', 'Name', 'Email', 'Mobile', 'Active', 'On Hold', 'Email Verified', 'Registered At',
]);

foreach ($users as $i => $user) {
    fputcsv($handle, [
        $i + 1,
        $user->name,
        $user->email,
        $user->mobile ?? '',
        ($user->is_active ?? false) ? 'Yes' : 'No',
        ($user->is_hold ?? false)   ? 'Yes' : 'No',
        ($user->email_verified ?? false) ? 'Yes' : 'No',
        optional($user->created_at)->format('Y-m-d H:i'),
    ]);
}
 
        fclose($handle);
    };
 
    return response()->stream($callback, 200, $headers);
}
public function assignAdmin(Request $request, $userId)
    {
        if (!Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Only Super Admin can assign users.');
        }

        $user = User::findOrFail($userId);
        $user->assigned_admin_id = $request->admin_id
            ? new \MongoDB\BSON\ObjectId($request->admin_id)
            : null;
        $user->save();

        return back()->with('success', 'User assigned successfully.');
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

    /**
     * Update document verification status
     */
    public function verifyDocument(Request $request, $userId, $docIndex)
    {
        $user = User::findOrFail($userId);
        $status = $request->input('status'); // 'verified', 'rejected', 'pending'

        $documents = $user->documents ?? [];

        if (!isset($documents[$docIndex])) {
            return redirect()->route('admin.users.edit', ['id' => $userId, 'active_tab' => 'documents'])
                ->with('error', 'Document not found.');
        }

        $documents[$docIndex]['status'] = $status;
        $documents[$docIndex]['verified_at'] = $status === 'verified' ? now()->toISOString() : null;
        $documents[$docIndex]['verified_by'] = $status === 'verified' ? Auth::id() : null;

        $user->update(['documents' => $documents]);

        $statusLabel = ucfirst($status);
        return redirect()->route('admin.users.edit', ['id' => $userId, 'active_tab' => 'documents'])
            ->with('success', "Document marked as {$statusLabel}.");
    }

    /**
     * Toggle listing active status from user edit page
     */
    public function toggleListingActive(Request $request, $userId, $listingId)
    {
        $listing = ProductListing::findOrFail($listingId);
        $listing->update(['is_active' => !$listing->is_active]);

        $msg = $listing->is_active ? 'Listing is now active.' : 'Listing is now on hold.';

        // Preserve listing filters
        $params = array_merge(
            ['id' => $userId, 'active_tab' => 'listings'],
            $request->only(['listing_filter', 'listing_status', 'listing_payment', 'listing_realtime'])
        );

        return redirect()->route('admin.users.edit', $params)
            ->with('success', $msg);
    }

    /**
     * Approve listing payment from user edit page
     */
    public function approveListingPayment(Request $request, $userId, $listingId)
    {
        $listing = ProductListing::findOrFail($listingId);
        $listing->update(['is_paid' => true]);

        // Preserve listing filters
        $params = array_merge(
            ['id' => $userId, 'active_tab' => 'listings'],
            $request->only(['listing_filter', 'listing_status', 'listing_payment', 'listing_realtime'])
        );

        return redirect()->route('admin.users.edit', $params)
            ->with('success', 'Payment approved successfully.');
    }

    /**
     * Approve listing from user edit page
     */
    public function approveListing(Request $request, $userId, $listingId)
    {
        $listing = ProductListing::findOrFail($listingId);
        $listing->update([
            'verification_status' => 'approved',
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => new \MongoDB\BSON\ObjectId(Auth::id()),
        ]);

        // Preserve listing filters
        $params = array_merge(
            ['id' => $userId, 'active_tab' => 'listings'],
            $request->only(['listing_filter', 'listing_status', 'listing_payment', 'listing_realtime'])
        );

        return redirect()->route('admin.users.edit', $params)
            ->with('success', 'Listing approved successfully.');
    }

    /**
     * Reject listing from user edit page
     */
    public function rejectListing(Request $request, $userId, $listingId)
    {
        $listing = ProductListing::findOrFail($listingId);
        $listing->update([
            'verification_status' => 'rejected',
            'is_active' => false,
        ]);

        // Preserve listing filters
        $params = array_merge(
            ['id' => $userId, 'active_tab' => 'listings'],
            $request->only(['listing_filter', 'listing_status', 'listing_payment', 'listing_realtime'])
        );

        return redirect()->route('admin.users.edit', $params)
            ->with('success', 'Listing rejected.');
    }
}
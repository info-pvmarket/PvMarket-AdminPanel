<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\User;
use App\Models\Order;
use App\Models\ProductListing;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductListingImage;
use App\Models\Role;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Services\AdminSubscriptionService;
use App\Services\CompanyDocumentStorageService;
use App\Services\ProductListingCsvExporter;
use App\Services\ListingUpdateService;
use App\Services\TranslationService;
use App\Traits\FiltersAssignedUsers;
use MongoDB\BSON\ObjectId;

class UserController extends Controller
{
    use FiltersAssignedUsers;

    public function __construct(
        protected TranslationService $translator,
        protected ListingUpdateService $listingUpdateService,
    ) {}

    public function index(Request $request)
    {
        $roleIds = Role::whereIn('slug', Role::ADMIN_SLUGS)->pluck('id');
        $roleObjectIds = $roleIds->map(fn($id) => new ObjectId((string) $id))->toArray();
        $roles = Role::whereNotIn('slug', Role::ADMIN_SLUGS)
            ->orderBy('role')
            ->get();

        // Start with users who are not admins (no role_id or role_id not in admin roles)
        $query = User::with(['role', 'assignedAdmin'])
            ->where(function ($q) use ($roleObjectIds) {
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

        $selectedRoleId = (string) $request->input('role_id', '');
        if ($roles->contains(fn(Role $role) => (string) $role->_id === $selectedRoleId)) {
            $query->where('role_id', new ObjectId($selectedRoleId));
        }

        $users = $query->orderBy('created_at', 'desc')
                       ->paginate($request->get('entries', 10));

        $userIds = $users->getCollection()
            ->map(fn(User $user) => new ObjectId((string) $user->_id))
            ->all();
        $companies = empty($userIds)
            ? collect()
            : Company::whereIn('user_id', $userIds)
                ->get()
                ->keyBy(fn(Company $company) => (string) $company->user_id);

        $users->getCollection()->each(
            fn(User $user) => $user->setRelation('company', $companies->get((string) $user->_id))
        );

        $admins = $this->getAdminsForAssignment();

        return view('admin.users.index', compact('users', 'admins', 'roles'));
    }

    public function toggleStatus(string $id)
    {
        $user = $this->managedUser($id);
        $isActive = !$user->isActiveForManagement();

        $user->syncActiveStatus($isActive);
        $user->is_hold = !$isActive;
        $user->save();

        if (!$isActive) {
            ProductListing::where('user_id', new ObjectId((string) $user->_id))
                ->update([
                    'is_hold' => true,
                    'is_active' => false,
                ]);
        }

        return back()->with(
            'success',
            'User status updated to ' . ($user->isActiveForManagement() ? 'Active.' : 'Inactive.')
        );
    }

    public function destroy(string $id)
    {
        $user = $this->managedUser($id);
        $userName = $user->name;
        $user->delete();

        return back()->with('success', "User {$userName} deleted successfully.");
    }

    private function managedUser(string $id): User
    {
        abort_unless(preg_match('/^[a-f\d]{24}$/i', $id), 404);

        $query = User::with('role')->where('_id', new ObjectId($id));

        if (!Auth::user()->isSuperAdmin()) {
            $query->where('assigned_admin_id', new ObjectId((string) Auth::id()));
        }

        $user = $query->firstOrFail();

        abort_if(
            in_array($user->role?->slug, Role::ADMIN_SLUGS, true),
            403,
            'Administrator accounts cannot be managed from User Management.'
        );

        return $user;
    }

    public function edit(Request $request, $id)
    {
        $user = $this->managedUser($id);
        $userId = new ObjectId($id);
        $company = Company::where('user_id', $userId)->first();

        $subscriptions = Subscription::where('user_id', $userId)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();
        $activeSubscription = $subscriptions->first(
            fn (Subscription $subscription) => $subscription->isActiveAt()
        );

        // ── Company Documents ─────────────────────────────────────────
        $documents = $company
            ? CompanyDocument::where('company_id', new ObjectId((string) $company->_id))
                ->whereNull('deleted_at')
                ->orderBy('uploaded_at', 'desc')
                ->get()
            : collect();

        // ── Listings (ProductListings by this user) ───────────────────
        $listingsQuery = ProductListing::where('user_id', $userId);

        [
            $listingFilter,
            $listingStatus,
            $listingPayment,
            $listingRealtime,
        ] = $this->applyUserListingFilters($listingsQuery, $request);

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
            'buyerUsersMap',
            'subscriptions',
            'activeSubscription'
        ) + compact('company'));
    }

    public function subscribeWithCoupon(
        Request $request,
        string $id,
        AdminSubscriptionService $subscriptions
    ) {
        $validated = $request->validate([
            'coupon_code' => ['required', 'string', 'max:50'],
        ]);

        $user = $this->managedUser($id);
        $admin = Auth::user();
        abort_unless($admin instanceof User, 403);

        $subscription = $subscriptions->subscribeWithCoupon(
            $user,
            $validated['coupon_code'],
            $admin
        );

        return redirect()->route('admin.users.edit', [
            'id' => $id,
            'active_tab' => 'subscriptions',
        ])->with(
            'success',
            "The {$subscription->plan_name} subscription was activated successfully."
        );
    }

    public function cancelSubscription(
        string $id,
        string $subscriptionId,
        AdminSubscriptionService $subscriptions
    ) {
        $user = $this->managedUser($id);
        $admin = Auth::user();
        abort_unless($admin instanceof User, 403);

        $subscriptions->cancel($user, $subscriptionId, $admin);

        return redirect()->route('admin.users.edit', [
            'id' => $id,
            'active_tab' => 'subscriptions',
        ])->with('success', 'The subscription was cancelled successfully.');
    }

    public function exportUserListings(
        Request $request,
        string $id,
        ProductListingCsvExporter $exporter
    ) {
        $user = $this->managedUser($id);
        $userId = new ObjectId((string) $user->_id);
        $query = ProductListing::where('user_id', $userId);

        $this->applyUserListingFilters($query, $request);

        return $exporter->download(
            $query->orderBy('created_at', 'desc')->get(),
            "user_{$id}_listings"
        );
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

    private function applyUserListingFilters($query, Request $request): array
    {
        $listingFilter = in_array(
            $request->input('listing_filter', 'all'),
            ['all', 'pending', 'verified', 'rejected'],
            true
        ) ? $request->input('listing_filter', 'all') : 'all';
        $listingStatus = in_array(
            $request->input('listing_status', 'all'),
            ['all', 'active', 'on_hold'],
            true
        ) ? $request->input('listing_status', 'all') : 'all';
        $listingPayment = in_array(
            $request->input('listing_payment', 'all'),
            ['all', 'paid', 'unpaid'],
            true
        ) ? $request->input('listing_payment', 'all') : 'all';
        $listingRealtime = in_array(
            $request->input('listing_realtime', 'all'),
            ['all', 'enabled', 'disabled'],
            true
        ) ? $request->input('listing_realtime', 'all') : 'all';

        if ($listingFilter !== 'all') {
            $query->where('verification_status', $listingFilter);
        }

        if ($listingStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($listingStatus === 'on_hold') {
            $query->where('is_active', false);
        }

        if ($listingPayment === 'paid') {
            $query->where('is_paid', true);
        } elseif ($listingPayment === 'unpaid') {
            $query->where('is_paid', false);
        }

        if ($listingRealtime === 'enabled') {
            $query->where('real_time_price', true);
        } elseif ($listingRealtime === 'disabled') {
            $query->where(function ($nested) {
                $nested->where('real_time_price', false)
                    ->orWhereNull('real_time_price');
            });
        }

        return [
            $listingFilter,
            $listingStatus,
            $listingPayment,
            $listingRealtime,
        ];
    }

    public function updateCompany(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $company = $this->companyForUser($user);

        $request->validate([
            'company_name' => 'nullable|string|max:255',
            'vat_id'       => 'nullable|string|max:100',
        ]);

        $companyName = $request->input('company_name');
        $vatId = $request->input('vat_id');
        $enableEditable = $request->boolean('enable_editable');
        $allowDocumentUpload = $request->boolean('allow_document_upload');
        $companyVerified = $request->boolean('company_verified');
        $showVerifiedBadge = $request->boolean('show_verified_batch');

        $company->fill([
            'company_name'        => $companyName,
            'name'                => $companyName,
            'vat_no'              => $vatId,
            'is_editable'         => $enableEditable,
            'allow_doc'           => $allowDocumentUpload,
            'seller_verified'     => $companyVerified,
            'company_verified'    => $companyVerified,
            'show_verified_batch' => $showVerifiedBadge,
            'is_active'           => $company->exists ? ($company->is_active ?? true) : true,
        ]);
        $company->save();

        $user->update([
            'company_name'           => $companyName,
            'vat_id'                 => $vatId,
            'enable_editable'        => $enableEditable,
            'allow_document_upload'  => $allowDocumentUpload,
            'company_verified'       => $companyVerified,
            'show_verified_batch'    => $showVerifiedBadge,
        ]);

        return redirect()->route('admin.users.edit', ['id' => $id, 'active_tab' => 'company'])
                         ->with('success', 'Company details updated.');
    }

    public function toggleCompanyVerified(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $company = $this->companyForUser($user);
        $nextVerified = !($company->seller_verified ?? $company->company_verified ?? $user->company_verified ?? false);

        $company->fill([
            'seller_verified'  => $nextVerified,
            'company_verified' => $nextVerified,
        ]);
        $company->save();

        $user->update(['company_verified' => $nextVerified]);

        // Preserve the current active tab from request
        $activeTab = $request->input('active_tab', 'basic');

        return redirect()->route('admin.users.edit', ['id' => $id, 'active_tab' => $activeTab])
                         ->with('success', 'Company verification status updated.');
    }

    private function companyForUser(User $user): Company
    {
        $userId = new ObjectId((string) $user->_id);

        $company = Company::where('user_id', $userId)->first();

        if ($company) {
            return $company;
        }

        return new Company([
            'user_id'      => $userId,
            'company_name' => $user->company_name ?? $user->name,
            'name'         => $user->company_name ?? $user->name,
            'vat_no'       => $user->vat_id ?? '',
            'is_active'    => true,
        ]);
    }

    public function export(Request $request)
{
    $roleIds = Role::whereIn('slug', Role::ADMIN_SLUGS)->pluck('id');
    $roleObjectIds = $roleIds->map(fn($id) => new ObjectId((string) $id))->toArray();
    $roles = Role::whereNotIn('slug', Role::ADMIN_SLUGS)->get();

    $query = User::with('role')
        ->where(function ($q) use ($roleObjectIds) {
            $q->whereNull('role_id')
              ->orWhereNotIn('role_id', $roleObjectIds);
        });

    if (!Auth::user()->isSuperAdmin()) {
        $query->where('assigned_admin_id', new ObjectId(Auth::id()));
    }
 
    // ── Apply the same filters as index ──────────────────────
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name',  'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    $selectedRoleId = (string) $request->input('role_id', '');
    if ($roles->contains(fn(Role $role) => (string) $role->_id === $selectedRoleId)) {
        $query->where('role_id', new ObjectId($selectedRoleId));
    }

    $users = $query->orderBy('created_at', 'desc')->get();
    $userIds = $users
        ->map(fn($user) => new ObjectId((string) $user->_id))
        ->values()
        ->all();

    $companies = empty($userIds)
        ? collect()
        : Company::whereIn('user_id', $userIds)
            ->get()
            ->keyBy(fn($company) => (string) $company->user_id);
 
    // ── Build CSV in memory ───────────────────────────────────
    $filename = 'users_companies_export_' . now()->format('Y-m-d_His') . '.csv';
 
    $headers = [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        'Pragma'              => 'no-cache',
        'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
        'Expires'             => '0',
    ];
 
    $callback = function () use ($users, $companies) {
        $handle = fopen('php://output', 'w');
 
        // UTF-8 BOM so Excel opens it correctly
        fputs($handle, "\xEF\xBB\xBF");
 
        // ── Column headers ────────────────────────────────────
        $yesNo = fn($value) => $value === null ? '' : ($value ? 'Yes' : 'No');
        $formatDate = function ($value) {
            if (!$value) {
                return '';
            }

            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d H:i');
            }

            try {
                return \Carbon\Carbon::parse($value)->format('Y-m-d H:i');
            } catch (\Throwable) {
                return (string) $value;
            }
        };

        fputcsv($handle, [
            'S.No',
            'Name',
            'Email',
            'Role',
            'Mobile',
            'Active',
            'On Hold',
            'Email Verified',
            'Registered At',
            'Company Name',
            'Company Type',
            'VAT/TAX ID',
            'Registered Country',
            'Country ID',
            'Company Address',
            'Company Active',
            'VAT Verified',
            'Seller Verified',
            'Company Verified',
            'Editable',
            'Document Upload Allowed',
            'Verified Badge',
            'Company Created At',
            'Company Updated At',
        ]);

        foreach ($users as $i => $user) {
            $company = $companies->get((string) $user->_id);

            fputcsv($handle, [
                $i + 1,
                $user->name ?? '',
                $user->email ?? '',
                $user->role_display_name,
                $user->mobile ?? '',
                $yesNo($user->is_active ?? false),
                $yesNo($user->is_hold ?? false),
                $yesNo($user->email_verified ?? false),
                $formatDate($user->created_at ?? null),
                $company->company_name ?? $company->name ?? $user->company_name ?? '',
                $company->company_type ?? '',
                $company->vat_no ?? $user->vat_id ?? '',
                $company->registered_country ?? '',
                $company?->country_id ? (string) $company->country_id : '',
                $company->address ?? '',
                $company ? $yesNo($company->is_active ?? false) : '',
                $company ? $yesNo($company->vat_verified ?? false) : '',
                $company ? $yesNo($company->seller_verified ?? false) : $yesNo($user->company_verified ?? null),
                $company ? $yesNo($company->company_verified ?? false) : $yesNo($user->company_verified ?? null),
                $company ? $yesNo($company->is_editable ?? false) : $yesNo($user->enable_editable ?? null),
                $company ? $yesNo($company->allow_doc ?? false) : $yesNo($user->allow_document_upload ?? null),
                $company ? $yesNo($company->show_verified_batch ?? false) : $yesNo($user->show_verified_batch ?? null),
                $company ? $formatDate($company->created_at ?? null) : '',
                $company ? $formatDate($company->updated_at ?? null) : '',
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
     * Upload company documents on behalf of a managed user.
     */
    public function uploadDocuments(
        Request $request,
        string $userId,
        CompanyDocumentStorageService $documentStorage,
    )
    {
        $user = $this->managedUser($userId);
        $company = Company::where(
            'user_id',
            new ObjectId((string) $user->_id)
        )->first();

        if (! $company) {
            return redirect()->route('admin.users.edit', [
                'id' => $userId,
                'active_tab' => 'documents',
            ])->with('error', 'Company details must be created before uploading documents.');
        }

        $validated = $request->validate([
            'document_type' => [
                'required',
                Rule::in(['Company License', 'VAT/TAX ID']),
            ],
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf,docx',
                'max:10240',
            ],
        ], [
            'document_type.required' => 'Please select a document type.',
            'document_type.in' => 'The selected document type is invalid.',
            'documents.required' => 'Please select at least one document.',
            'documents.*.mimes' => 'Documents must be JPG, PNG, PDF, or DOCX files.',
            'documents.*.max' => 'Each document must not exceed 10 MB.',
        ]);

        $storedPaths = [];
        $storedDocuments = [];

        try {
            foreach ($request->file('documents', []) as $file) {
                $metadata = $documentStorage->store(
                    $file,
                    (string) $company->_id,
                );
                $storedPaths[] = $metadata['path'];
                $storedDocuments[] = CompanyDocument::create([
                    'company_id' => new ObjectId((string) $company->_id),
                    'document_type' => $validated['document_type'],
                    ...$metadata,
                    'is_verified' => false,
                ]);
            }
        } catch (\Throwable $exception) {
            foreach ($storedDocuments as $document) {
                $document->delete();
            }
            foreach ($storedPaths as $path) {
                $documentStorage->delete($path);
            }

            report($exception);

            return redirect()->route('admin.users.edit', [
                'id' => $userId,
                'active_tab' => 'documents',
            ])->withInput()->with('error', 'The documents could not be uploaded. Please try again.');
        }

        $count = count($storedDocuments);

        return redirect()->route('admin.users.edit', [
            'id' => $userId,
            'active_tab' => 'documents',
        ])->with('success', "Successfully uploaded {$count} document(s).");
    }

    /**
     * Update document verification status
     */
    public function verifyDocument(Request $request, $userId, $docIndex)
    {
        $user = User::findOrFail($userId);
        $request->validate([
            'status' => 'required|in:verified,pending',
        ]);

        $status = $request->input('status');
        $company = Company::where('user_id', new ObjectId((string) $user->_id))->first();

        try {
            $documentId = new ObjectId($docIndex);
        } catch (\Throwable $e) {
            $documentId = null;
        }

        $document = $company && $documentId
            ? CompanyDocument::where('_id', $documentId)
                ->where('company_id', new ObjectId((string) $company->_id))
                ->first()
            : null;
        if (!$document) {
            return redirect()->route('admin.users.edit', ['id' => $userId, 'active_tab' => 'documents'])
                ->with('error', 'Document not found.');
        }

        $document->update([
            'is_verified' => $status === 'verified',
        ]);

        $statusLabel = $status === 'verified' ? 'Verified' : 'Pending';
        return redirect()->route('admin.users.edit', ['id' => $userId, 'active_tab' => 'documents'])
            ->with('success', "Document marked as {$statusLabel}.");
    }

    /**
     * Toggle listing active status from user edit page
     */
    public function toggleListingActive(Request $request, $userId, $listingId)
    {
        $listing = ProductListing::findOrFail($listingId);
        $attributes = $this->listingUpdateService->requireReapproval(
            ['is_active' => ! $listing->is_active],
            Auth::user()?->isSuperAdmin() ?? false,
        );
        $listing->update($attributes);

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
            'verification_status' => 'verified',
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MainMenu;
use App\Models\SubMenu;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Warehouse;
use App\Models\Commission;
use App\Models\Country;
use App\Services\TranslationService;
use App\Models\ProductListingImage;
use App\Models\Incoterm;
use App\Models\Currency;
use App\Traits\FiltersAssignedUsers;
use App\Services\ProductListingCsvExporter;
use App\Services\ListingUpdateService;

class ProductListingController extends Controller
{
    use FiltersAssignedUsers;

    public function __construct(
        protected TranslationService $translator,
        protected ListingUpdateService $listingUpdateService,
    ) {}
    // ── Index (My Listings page) ────────────────────────────────────

    public function index(Request $request)
    {
        try {
            return $this->renderIndex($request);
        } catch (\Throwable $exception) {
            $diagnosticUser = Auth::user();
            $canViewDiagnostic = ($diagnosticUser?->isSuperAdmin() ?? false)
                || strtolower((string) $diagnosticUser?->email) === 'info@pv.market';

            if (! $canViewDiagnostic) {
                throw $exception;
            }

            $message = str_replace(base_path(), '[app]', $exception->getMessage());
            $message = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[email]', $message) ?? '';
            $message = preg_replace('/\b[a-f0-9]{24}\b/i', '[object-id]', $message) ?? '';
            $message = preg_replace('/[\x00-\x1F\x7F]+/', ' ', $message) ?? '';

            $errorClass = class_basename($exception);

            return response("Server Error\n{$errorClass}\n{$message}", 500)
                ->header('X-PV-Error-Class', class_basename($exception))
                ->header('X-PV-Error-Message', rawurlencode(substr($message, 0, 700)));
        }
    }

    private function renderIndex(Request $request)
    {
        $userId = (string) Auth::id();   // ← cast to string to match MongoDB stored value

        //$query = ProductListing::where('user_id', new \MongoDB\BSON\ObjectId(Auth::id()));

        $query = ProductListing::query();

        // Filter by assigned users
        $this->filterByAssignedUsers($query, 'user_id');

        if ($request->filled('listing_id')) {
            $query->whereIn('_id', $this->mongoIdCandidates((string) $request->input('listing_id')));
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            if ($search !== '') {
                $productIdCandidates = $this->searchProductIdCandidates($search);

                $query->where(function ($q) use ($search, $productIdCandidates) {
                    $q->where('sku_code', 'like', "%{$search}%");

                    if (!empty($productIdCandidates)) {
                        $q->orWhereIn('product_id', $productIdCandidates);
                    }
                });
            }
        }


        $filter        = $request->get('filter', 'all');
        $statusFilter  = $request->get('status_filter', 'all');
        $paymentFilter = $request->get('payment_filter', 'all');
        $warehouseFilter = $request->get('warehouse_id');
        $realTimePriceFilter = $request->get('real_time_price', 'all');
        $categoryFilter = trim((string) $request->get('category_id', ''));
        $subCategoryFilter = trim((string) $request->get('sub_category_id', ''));
        $brandFilter = trim((string) $request->get('brand_id', ''));

        $this->applyProductFilters(
            $query,
            $categoryFilter,
            $subCategoryFilter,
            $brandFilter,
        );

        // Verification status
        if ($filter !== 'all') {
            $query->where('verification_status', $filter);
        }

        // Active / On Hold
        if ($statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($statusFilter === 'on_hold') {
            $query->where('is_active', false);
        }

        // Paid / Unpaid
        if ($paymentFilter === 'paid') {
            $query->where('is_paid', true);
        } elseif ($paymentFilter === 'unpaid') {
            $query->where('is_paid', false);
        }

        // Real-Time Price
        if ($realTimePriceFilter === 'yes') {
            $query->where('real_time_price', true);
        } elseif ($realTimePriceFilter === 'no') {
            $query->where('real_time_price', false);
        }

        // Warehouse
        if ($warehouseFilter) {
            $query->whereIn('warehouse_id', $this->mongoIdCandidates($warehouseFilter));
        }

        //$listings = $query->latest()->paginate(10)->withQueryString();


        $listings = $query
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $unpaidCount = ProductListing::where('user_id', new \MongoDB\BSON\ObjectId(Auth::id()))
            ->where('is_paid', false)
            ->count();

        $warehouses = Warehouse::where(function ($q) {
            $q->where('is_paid', true)
                ->orWhere('payment_status', 'paid');
        })
            ->orderBy('warehouse_name')
            ->get();

        $filterCategories = MainMenu::availableForDropdown()
            ->orderBy('category_name')
            ->get();

        $filterSubCategoriesQuery = SubMenu::availableForDropdown()
            ->orderBy('sub_category_name');

        if ($categoryFilter !== '') {
            $filterSubCategoriesQuery->whereIn(
                'category_id',
                $this->mongoIdCandidates($categoryFilter),
            );
        }

        $filterSubCategories = $filterSubCategoriesQuery->get();

        $filterBrandsQuery = Brand::where('is_active', true)
            ->orderBy('name');

        if ($categoryFilter !== '' || $subCategoryFilter !== '') {
            $brandProductQuery = Product::query();

            if ($categoryFilter !== '') {
                $brandProductQuery->whereIn(
                    'category_id',
                    $this->mongoIdCandidates($categoryFilter),
                );
            }

            if ($subCategoryFilter !== '') {
                $brandProductQuery->whereIn(
                    'sub_category_id',
                    $this->mongoIdCandidates($subCategoryFilter),
                );
            }

            $brandIdCandidates = $brandProductQuery
                ->get(['brand_id'])
                ->pluck('brand_id')
                ->filter()
                ->flatMap(fn($id) => $this->mongoIdCandidates($id))
                ->unique(fn($id) => is_object($id) ? get_class($id) . ':' . (string) $id : 'string:' . $id)
                ->values()
                ->all();

            $filterBrandsQuery->whereIn(
                '_id',
                !empty($brandIdCandidates) ? $brandIdCandidates : ['__no_matching_brand__'],
            );
        }

        $filterBrands = $filterBrandsQuery->get();

        $productIds   = $listings->pluck('product_id')->filter()->unique()
            ->map(fn($id) => (string)$id)->values();
        $warehouseIds = $listings->pluck('warehouse_id')->filter()->unique()
            ->map(fn($id) => (string)$id)->values();

        $productsMap   = Product::whereIn('_id', $productIds)->get()
            ->keyBy(fn($p) => (string)$p->_id);

        $warehousesMap = Warehouse::whereIn('_id', $warehouseIds)->get()
            ->keyBy(fn($w) => (string)$w->_id);

        $countryIds = $warehousesMap->pluck('country')
            ->filter()
            ->flatMap(fn($id) => $this->mongoIdCandidates($id))
            ->unique(fn($id) => is_object($id) ? get_class($id) . ':' . (string)$id : 'string:' . $id)
            ->values()
            ->all();

        $countriesMap = Country::whereIn('_id', $countryIds)->get()
            ->keyBy(fn($country) => (string)$country->_id);

        $incotermIds = $listings->pluck('incoterms_id')
            ->filter()
            ->flatMap(fn($id) => $this->mongoIdCandidates($id))
            ->unique(fn($id) => is_object($id) ? get_class($id) . ':' . (string)$id : 'string:' . $id)
            ->values()
            ->all();

        $incotermsMap = Incoterm::whereIn('_id', $incotermIds)->get()
            ->keyBy(fn($incoterm) => (string)$incoterm->_id);

        $userIds  = $listings->pluck('user_id')->filter()->unique()
            ->map(fn($id) => (string)$id)->values();
        $usersMap = \App\Models\User::whereIn('_id', $userIds)->get()
            ->keyBy(fn($u) => (string)$u->_id);

        $listingIds = $listings->pluck('_id')
            ->map(fn($id) => new \MongoDB\BSON\ObjectId((string)$id))
            ->toArray();

        $imagesMap = ProductListingImage::whereIn('product_listing_id', $listingIds)
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn($img) => (string)$img->product_listing_id);

        return view('admin.product_listing.index', compact(
            'listings',
            'unpaidCount',
            'statusFilter',
            'paymentFilter',
            'warehouses',
            'warehouseFilter',
            'filter',
            'realTimePriceFilter',
            'productsMap',
            'warehousesMap',
            'countriesMap',
            'incotermsMap',
            'usersMap',
            'imagesMap',
            'categoryFilter',
            'subCategoryFilter',
            'brandFilter',
            'filterCategories',
            'filterSubCategories',
            'filterBrands',
        ));
    }

    // ── Create ──────────────────────────────────────────────────────

    public function export(Request $request, ProductListingCsvExporter $exporter)
    {
        $query = ProductListing::query();
        $this->filterByAssignedUsers($query, 'user_id');

        if ($request->filled('search')) {
            $search = trim($request->search);

            if ($search !== '') {
                $productIdCandidates = $this->searchProductIdCandidates($search);

                $query->where(function ($q) use ($search, $productIdCandidates) {
                    $q->where('sku_code', 'like', "%{$search}%");

                    if (!empty($productIdCandidates)) {
                        $q->orWhereIn('product_id', $productIdCandidates);
                    }
                });
            }
        }

        $filter = $request->get('filter', 'all');
        $statusFilter = $request->get('status_filter', 'all');
        $paymentFilter = $request->get('payment_filter', 'all');
        $warehouseFilter = $request->get('warehouse_id');
        $realTimePriceFilter = $request->get('real_time_price', 'all');
        $categoryFilter = trim((string) $request->get('category_id', ''));
        $subCategoryFilter = trim((string) $request->get('sub_category_id', ''));
        $brandFilter = trim((string) $request->get('brand_id', ''));

        $this->applyProductFilters(
            $query,
            $categoryFilter,
            $subCategoryFilter,
            $brandFilter,
        );

        if ($filter !== 'all') {
            $query->where('verification_status', $filter);
        }

        if ($statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($statusFilter === 'on_hold') {
            $query->where('is_active', false);
        }

        if ($paymentFilter === 'paid') {
            $query->where('is_paid', true);
        } elseif ($paymentFilter === 'unpaid') {
            $query->where('is_paid', false);
        }

        if ($realTimePriceFilter === 'yes') {
            $query->where('real_time_price', true);
        } elseif ($realTimePriceFilter === 'no') {
            $query->where('real_time_price', false);
        }

        if ($warehouseFilter) {
            $query->whereIn('warehouse_id', $this->mongoIdCandidates($warehouseFilter));
        }

        return $exporter->download(
            $query
                ->orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    /**
     * Find products by name, SKU, or brand and return both MongoDB ObjectId
     * and string candidates for legacy listing references.
     */
    private function searchProductIdCandidates(string $search): array
    {
        $brandIdCandidates = Brand::where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        })
            ->get(['_id'])
            ->flatMap(fn($brand) => $this->mongoIdCandidates($brand->_id))
            ->unique(fn($id) => is_object($id) ? get_class($id) . ':' . (string) $id : 'string:' . $id)
            ->values()
            ->all();

        return Product::where(function ($query) use ($search, $brandIdCandidates) {
            $query->where('product_name', 'like', "%{$search}%")
                ->orWhere('sku_code', 'like', "%{$search}%")
                ->orWhere('brand_name', 'like', "%{$search}%");

            if (!empty($brandIdCandidates)) {
                $query->orWhereIn('brand_id', $brandIdCandidates);
            }
        })
            ->get(['_id'])
            ->flatMap(fn($product) => $this->mongoIdCandidates($product->_id))
            ->unique(fn($id) => is_object($id) ? get_class($id) . ':' . (string) $id : 'string:' . $id)
            ->values()
            ->all();
    }

    /**
     * Restrict listings to products matching the selected catalog filters.
     * Both ObjectId and string candidates are used for legacy references.
     */
    private function applyProductFilters(
        $listingQuery,
        string $categoryId,
        string $subCategoryId,
        string $brandId,
    ): void {
        if ($categoryId === '' && $subCategoryId === '' && $brandId === '') {
            return;
        }

        $productQuery = Product::query();

        if ($categoryId !== '') {
            $productQuery->whereIn(
                'category_id',
                $this->mongoIdCandidates($categoryId),
            );
        }

        if ($subCategoryId !== '') {
            $productQuery->whereIn(
                'sub_category_id',
                $this->mongoIdCandidates($subCategoryId),
            );
        }

        if ($brandId !== '') {
            $productQuery->whereIn(
                'brand_id',
                $this->mongoIdCandidates($brandId),
            );
        }

        $productIdCandidates = $productQuery
            ->get(['_id'])
            ->flatMap(fn($product) => $this->mongoIdCandidates($product->_id))
            ->unique(fn($id) => is_object($id) ? get_class($id) . ':' . (string) $id : 'string:' . $id)
            ->values()
            ->all();

        $listingQuery->whereIn(
            'product_id',
            !empty($productIdCandidates) ? $productIdCandidates : ['__no_matching_product__'],
        );
    }

    public function create()
    {
        $mainCategories = MainMenu::availableForDropdown()->orderBy('category_name')->get();
        $subCategories  = SubMenu::availableForDropdown()->orderBy('sub_category_name')->get();
        $products       = Product::all();
        $warehouses     = Warehouse::all();
        $commissions    = Commission::all(['category_id', 'category_name', 'commission_percentage']);

        $sellTypes     = ['sell by pieces', 'sell by containers', 'sell by weight'];
        $currencies    = $this->availableCurrencies();
        $discountTypes = ['No Promotion', 'fixed', 'percentage'];
        $incoterms = Incoterm::orderBy('name')->get();

        $commissionsJson = $commissions->map(fn($c) => [
            'category_id'   => (string)$c->category_id,
            'category_name' => $c->category_name,
            'percentage'    => $c->commission_percentage,
        ])->values();

        return view('admin.product_listing.create', compact(
            'mainCategories',
            'subCategories',
            'products',
            'warehouses',
            'sellTypes',
            'currencies',
            'discountTypes',
            'incoterms',
            'commissions',
            'commissionsJson',
        ));
    }

    // ── Store ───────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'                       => 'required|string',
            'main_category_id'                 => 'required|string',
            'sub_category_id'                  => 'required|string',
            'warehouse_id'                     => 'required|string',
            'sell_type'                        => 'required|string',
            'currency_id'                      => 'required|string',
            'discount_type'                    => 'nullable|string',
            'incoterm_id'                      => 'required|string',
            'slug'                             => 'nullable|string|max:255',
            'total_quantity'                   => 'required|integer|min:1',
            'lead_time'                        => 'required|integer|min:0',
            'is_on_hold'                       => 'nullable|boolean',
            'is_solar_listing'                 => 'nullable|boolean',
            'solar_tier'                       => 'nullable|required_if:is_solar_listing,1|in:premium,recommended,value',
            'solar_grid_types'                 => 'nullable|required_if:is_solar_listing,1|array|min:1',
            'solar_grid_types.*'               => 'in:on-grid,off-grid,hybrid',
            'solar_phase_types'                => 'nullable|required_if:is_solar_listing,1|array|min:1',
            'solar_phase_types.*'              => 'in:single,three',
            'images.*'                         => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'slots'                            => 'required|array|min:1',
            'slots.*.min_quantity'             => 'required|integer|min:0',
            'slots.*.max_quantity'             => 'nullable|integer|min:1',
            'slots.*.price'                    => 'required|numeric|min:0',
            'slots.*.commission_percentage'    => 'nullable|numeric|min:0',  // ← new
            'slots.*.total_price'              => 'nullable|numeric|min:0',  // ← new
        ]);

        $validated['product_id']       = new \MongoDB\BSON\ObjectId($validated['product_id']);
        $validated['main_category_id'] = new \MongoDB\BSON\ObjectId($validated['main_category_id']);
        $validated['sub_category_id']  = new \MongoDB\BSON\ObjectId($validated['sub_category_id']);
        $validated['warehouse_id']     = new \MongoDB\BSON\ObjectId($validated['warehouse_id']);
        $validated['user_id']             = new \MongoDB\BSON\ObjectId(Auth::id());
        $validated['created_by']          = new \MongoDB\BSON\ObjectId(Auth::id());
        $validated['verification_status'] = 'pending';
        $validated['incoterms_id'] = new \MongoDB\BSON\ObjectId($validated['incoterm_id']);
        unset($validated['incoterm_id']);
        $validated['slug']            = $request->filled('slug')
            ? \Illuminate\Support\Str::slug($request->slug)
            : \Illuminate\Support\Str::slug($request->product_id . '-' . time());
        $validated['real_time_price'] = $request->boolean('real_time_price', false);
        $validated['is_active']           = ! $request->boolean('is_on_hold', false);
        unset($validated['is_on_hold']);
        $validated['is_paid']             = false;
        $validated['is_sold_off']         = $request->boolean('is_sold_off', false);
        $validated['is_popular']          = $request->boolean('is_popular', false);
        $validated['is_solar_listing']    = $request->boolean('is_solar_listing', false);
        $validated['solar_tier']          = $validated['is_solar_listing'] ? $request->input('solar_tier') : null;
        $validated['solar_grid_types']    = $validated['is_solar_listing'] ? array_values($request->input('solar_grid_types', [])) : [];
        $validated['solar_phase_types']   = $validated['is_solar_listing'] ? array_values($request->input('solar_phase_types', [])) : [];
        $validated['sku_code']            = 'PV-' . rand(1000000000, 9999999999) . '-' . rand(1000, 9999);

        // Convert max_quantity: empty string → null
        $slotsAsObjects = [];
        foreach ($validated['slots'] as $slot) {
            $price      = (float) $slot['price'];
            $commission = isset($slot['commission_percentage']) && $slot['commission_percentage'] !== ''
                ? (float) $slot['commission_percentage']
                : 0;
            $totalPrice = isset($slot['total_price']) && $slot['total_price'] !== ''
                ? (float) $slot['total_price']
                : round($price + ($price * $commission / 100), 2); // fallback calculation

            $slotsAsObjects[] = (object) [
                'min_quantity'          => (int) $slot['min_quantity'],
                'max_quantity'          => (isset($slot['max_quantity']) && $slot['max_quantity'] !== '')
                    ? (int) $slot['max_quantity']
                    : null,
                'price'                 => $price,
                'commission_percentage' => $commission,
                'total_price'           => $totalPrice,
            ];
        }

        $validated['slots'] = $slotsAsObjects;
        // Remove images from validated — stored separately
        unset($validated['images']);

        $listing = ProductListing::create($validated);

        // Store images in separate collection
        if ($request->hasFile('images')) {
            $sortOrder = 1;
            foreach ($request->file('images') as $file) {
                $filename     = time() . '_' . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path         = 'product-listings/' . $filename;
                $file->storeAs('product-listings', $filename, 'public');

                ProductListingImage::create([
                    'product_listing_id' => new \MongoDB\BSON\ObjectId((string)$listing->_id),
                    'image'              => [
                        'size'          => $file->getSize(),
                        'uploaded_at'   => now()->toISOString(),
                        'filename'      => $filename,
                        'original_name' => $file->getClientOriginalName(),
                        'path'          => $path,
                        'url'           => $path,
                        'mime_type'     => $file->getMimeType(),
                    ],
                    'sort_order'  => $sortOrder++,
                    'created_by'  => new \MongoDB\BSON\ObjectId(Auth::id()),
                ]);
            }
        }

        $productName = Product::find($request->product_id)?->product_name
            ?? $listing->sku_code
            ?? 'Unknown product';
        $createdBy = Auth::user()?->email ?? 'Unknown user';
        $this->listingUpdateService->notifyCreated((string) $productName, (string) $createdBy);

        return redirect()->route('product_listing.index')
            ->with('success', 'Your listing has been created and is pending approval.');
    }

    // ── Show ────────────────────────────────────────────────────────

    public function show(string $id)
    {
        $listing = ProductListing::findOrFail($id);
        return view('admin.product_listing.show', compact('listing'));
    }

    // ── Edit ────────────────────────────────────────────────────────

    public function edit(string $id)
    {
        $listing     = ProductListing::findOrFail($id);
        $commissions = Commission::all(['category_id', 'category_name', 'commission_percentage']);

        $commissionsJson = $commissions->map(fn($c) => [
            'category_id'   => (string)$c->category_id,
            'category_name' => $c->category_name,
            'percentage'    => $c->commission_percentage,
        ])->values();

        // ── Resolve names from IDs ──────────────────────────────
        $mainCatId    = (string)$listing->main_category_id;
        $subCatId     = (string)$listing->sub_category_id;
        $productId    = (string)$listing->product_id;
        $warehouseId  = (string)$listing->warehouse_id;
        $mainCategories = MainMenu::availableForDropdown()->orderBy('category_name')->get();
        $subCategories  = SubMenu::availableForDropdown()->orderBy('sub_category_name')->get();
        $products       = Product::all();
        $warehouses     = Warehouse::all();

        $mainCategory = MainMenu::where('_id', new \MongoDB\BSON\ObjectId($mainCatId))->first();
        $subCategory  = SubMenu::where('_id', new \MongoDB\BSON\ObjectId($subCatId))->first();
        $product      = Product::where('_id', new \MongoDB\BSON\ObjectId($productId))->first();
        $warehouse    = Warehouse::where('_id', new \MongoDB\BSON\ObjectId($warehouseId))->first();
        $sellTypes = [
            'sell by pieces' => 'Sell By Pieces Only',
            'sell by pallets' => 'Sell By Pallets Only',
            'sell by containers' => 'Sell By Containers Only',
        ];
        $currencies    = $this->availableCurrencies();
        $listingCurrency = strtoupper(trim((string) $listing->currency_id));
        if ($listingCurrency !== '' && ! in_array($listingCurrency, $currencies, true)) {
            $currencies[] = $listingCurrency;
            sort($currencies);
        }
        $discountTypes = ['No Promotion', 'fixed', 'percentage'];
        $incoterms = Incoterm::orderBy('name')->get();

        $inventoryHistory = \App\Models\InventoryTransaction::where(
            'listing_id',
            new \MongoDB\BSON\ObjectId($id)
        )
            ->whereIn('transaction_type', ['initial_stock', 'stock_add', 'stock_reduce'])
            ->orderBy('created_at', 'desc')
            ->get();

        $currentStock = \App\Models\InventoryTransaction::currentStock($id);

        // Fetch images explicitly
        $listingImages = ProductListingImage::where('product_listing_id', new \MongoDB\BSON\ObjectId($id))
            ->orderBy('sort_order')
            ->get();

        $selectedSolarGridTypes = $this->normalizeStringArray(old('solar_grid_types', $listing->solar_grid_types ?? []));
        $selectedSolarPhaseTypes = $this->normalizeStringArray(old('solar_phase_types', $listing->solar_phase_types ?? []));
        $isOfferOnHold = ! filter_var($listing->is_active ?? true, FILTER_VALIDATE_BOOLEAN);

        return view('admin.product_listing.edit', compact(
            'listing',
            'sellTypes',
            'currencies',
            'discountTypes',
            'incoterms',
            'commissions',
            'commissionsJson',
            'mainCategory',
            'subCategory',
            'product',
            'warehouse',
            'mainCategories',
            'subCategories',
            'products',
            'warehouses',
            'inventoryHistory',
            'currentStock',
            'listingImages',
            'selectedSolarGridTypes',
            'selectedSolarPhaseTypes',
            'isOfferOnHold',
        ));
    }
    // ── Update ──────────────────────────────────────────────────────

    public function update(Request $request, string $id)
    {
        $listing = ProductListing::findOrFail($id);

        $validated = $request->validate([
            'sell_type'                        => 'required|string|in:sell by pieces,sell by pallets,sell by containers',
            'currency_id'                      => 'required|string',
            'discount_type'                    => 'nullable|string',
            'incoterm_id' => 'required|string',
            'slug'        => 'nullable|string|max:255',
            'total_quantity'                   => 'required|integer|min:1',
            'lead_time'                        => 'required|integer|min:0',
            'is_on_hold'                       => 'nullable|boolean',
            'is_solar_listing'                 => 'nullable|boolean',
            'solar_tier'                       => 'nullable|required_if:is_solar_listing,1|in:premium,recommended,value',
            'solar_grid_types'                 => 'nullable|required_if:is_solar_listing,1|array|min:1',
            'solar_grid_types.*'               => 'in:on-grid,off-grid,hybrid',
            'solar_phase_types'                => 'nullable|required_if:is_solar_listing,1|array|min:1',
            'solar_phase_types.*'              => 'in:single,three',
            'slots'                            => 'required|array|min:1',
            'slots.*.min_quantity'             => 'required|integer|min:0',
            'slots.*.max_quantity'             => 'nullable|integer|min:1',
            'slots.*.price'                    => 'required|numeric|min:0',
            'slots.*.commission_percentage'    => 'nullable|numeric|min:0',  // ← new
            'slots.*.total_price'              => 'nullable|numeric|min:0',  // ← new
            'main_category_id' => 'required|string',
            'sub_category_id'  => 'required|string',
            'product_id'       => 'required|string',
            'warehouse_id'     => 'required|string',
            'images.*'                         => 'nullable|image|mimes:jpeg,png,webp|max:5120',
        ]);
        $validated['warehouse_id'] = new \MongoDB\BSON\ObjectId($listing->warehouse_id);
        $validated['is_active']   = ! $request->boolean('is_on_hold', false);
        unset($validated['is_on_hold']);
        $validated['is_sold_off'] = $request->boolean('is_sold_off', false);
        $validated['is_popular']  = $request->boolean('is_popular', false);
        $validated['is_solar_listing'] = $request->boolean('is_solar_listing', false);
        $validated['solar_tier'] = $validated['is_solar_listing'] ? $request->input('solar_tier') : null;
        $validated['solar_grid_types'] = $validated['is_solar_listing'] ? array_values($request->input('solar_grid_types', [])) : [];
        $validated['solar_phase_types'] = $validated['is_solar_listing'] ? array_values($request->input('solar_phase_types', [])) : [];
        $validated['incoterms_id'] = new \MongoDB\BSON\ObjectId($request->incoterm_id);
        unset($validated['incoterm_id']);
        $validated['slug']            = $request->filled('slug')
            ? \Illuminate\Support\Str::slug($request->slug)
            : $listing->slug;
        $validated['real_time_price'] = $request->boolean('real_time_price', false);
        $validated['main_category_id'] = new \MongoDB\BSON\ObjectId($request->main_category_id);
        $validated['sub_category_id']  = new \MongoDB\BSON\ObjectId($request->sub_category_id);
        $validated['product_id']       = new \MongoDB\BSON\ObjectId($request->product_id);
        $validated['warehouse_id']     = new \MongoDB\BSON\ObjectId($request->warehouse_id);

        // Convert max_quantity: empty string → null
        $slotsAsObjects = [];
        foreach ($validated['slots'] as $slot) {
            $price      = (float) $slot['price'];
            $commission = isset($slot['commission_percentage']) && $slot['commission_percentage'] !== ''
                ? (float) $slot['commission_percentage']
                : 0;
            $totalPrice = isset($slot['total_price']) && $slot['total_price'] !== ''
                ? (float) $slot['total_price']
                : round($price + ($price * $commission / 100), 2);

            $slotsAsObjects[] = (object) [
                'min_quantity'          => (int) $slot['min_quantity'],
                'max_quantity'          => (isset($slot['max_quantity']) && $slot['max_quantity'] !== '')
                    ? (int) $slot['max_quantity']
                    : null,
                'price'                 => $price,
                'commission_percentage' => $commission,
                'total_price'           => $totalPrice,
            ];
        }
        $validated['slots'] = $slotsAsObjects;
        // Remove images from validated — stored separately
        unset($validated['images']);

        // Any seller or administrator edit requires a fresh approval. The
        // dedicated approval action remains responsible for marking it verified.
        $isSuperAdmin = Auth::user()?->isSuperAdmin() ?? false;

        // Seller and regular-admin edits require fresh approval. A super-admin
        // edit preserves the listing's existing verification status.
        $validated = $this->listingUpdateService->requireReapproval($validated, $isSuperAdmin);

        if ($request->hasFile('images')) {
            $lastOrder = ProductListingImage::where(
                'product_listing_id',
                new \MongoDB\BSON\ObjectId((string)$listing->_id)
            )->max('sort_order') ?? 0;

            foreach ($request->file('images') as $file) {
                $filename = time() . '_' . rand(1000, 9999) . '_' . $file->getClientOriginalName();
                $path     = 'product-listings/' . $filename;
                $file->storeAs('product-listings', $filename, 'public');

                ProductListingImage::create([
                    'product_listing_id' => new \MongoDB\BSON\ObjectId((string)$listing->_id),
                    'image'              => [
                        'size'          => $file->getSize(),
                        'uploaded_at'   => now()->toISOString(),
                        'filename'      => $filename,
                        'original_name' => $file->getClientOriginalName(),
                        'path'          => $path,
                        'url'           => $path,
                        'mime_type'     => $file->getMimeType(),
                    ],
                    'sort_order'  => ++$lastOrder,
                    'created_by'  => new \MongoDB\BSON\ObjectId(Auth::id()),
                ]);
            }
        }

        $listing->update($validated);

        $productName = Product::find($request->product_id)?->product_name
            ?? $listing->sku_code
            ?? 'Unknown product';
        $updatedBy = Auth::user()?->email ?? 'Unknown user';
        $this->listingUpdateService->notify(
            (string) $productName,
            (string) $updatedBy,
            $isSuperAdmin,
        );

        return redirect()->route('product_listing.index')
            ->with('success', 'Listing updated successfully.');
    }

    // ── Destroy ─────────────────────────────────────────────────────

    public function destroy(string $id)
    {
        $listing = ProductListing::findOrFail($id);

        ProductListingImage::where(
            'product_listing_id',
            new \MongoDB\BSON\ObjectId($id)
        )->delete();

        $listing->delete();

        return redirect()->route('product_listing.index')
            ->with('success', 'Listing deleted successfully.');
    }

    // ── Toggle Hold / Active ────────────────────────────────────────

    public function toggleActive(string $id)
    {
        $listing = ProductListing::findOrFail($id);
        $listing->update(['is_active' => !$listing->is_active]);

        $msg = $listing->is_active ? 'Listing is now active.' : 'Listing is now inactive.';
        return back()->with('success', $msg);
    }

    // ── API: Sub-categories by Main Category ────────────────────
    public function getSubCategories(string $mainCategoryId)
    {
        $subCategories = SubMenu::availableForDropdown()
            ->where('category_id', new \MongoDB\BSON\ObjectId($mainCategoryId))
            ->orderBy('sub_category_name')
            ->get();
        return response()->json($subCategories);
    }

    public function getProducts(string $subCategoryId)
    {
        try {
            $products = Product::where('sub_category_id', new \MongoDB\BSON\ObjectId($subCategoryId))
                ->get(['_id', 'product_name']);
            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── Approve Payment ─────────────────────────────────────────────

    public function approvePayment(string $id)
    {
        $listing = ProductListing::findOrFail($id);
        $listing->update(['is_paid' => true]);

        return back()->with('success', 'Payment approved successfully.');
    }

    // ── Approve Listing ─────────────────────────────────────────────

    public function approveListing(string $id)
    {
        $listing = ProductListing::findOrFail($id);
        $listing->update([
            'verification_status' => 'verified',
            'is_active'           => true,
        ]);

        return back()->with('success', 'Listing verified successfully.');
    }

    // ── API: All Warehouses ─────────────────────────────────────
    public function getWarehouses()
    {
        return response()->json(Warehouse::all(['id', 'name']));
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
                        $data[$field],
                        $locale,
                        'en'
                    );
                }
            }

            if (!empty($translated)) {
                $data[$locale] = $translated;
            }
        }

        return $data;
    }

    private function mongoIdCandidates($id): array
    {
        $stringId = is_object($id) && method_exists($id, '__toString')
            ? (string) $id
            : (string) $id;

        if ($stringId === '') {
            return [];
        }

        $candidates = [$stringId];

        if (preg_match('/^[a-f\d]{24}$/i', $stringId)) {
            $candidates[] = new \MongoDB\BSON\ObjectId($stringId);
        }

        return $candidates;
    }

    /**
     * Return the common currency list for admin listing forms.
     *
     * @return array<int, string>
     */
    private function availableCurrencies(): array
    {
        return Currency::orderBy('code')
            ->pluck('code')
            ->map(static fn ($code) => strtoupper(trim((string) $code)))
            ->filter()
            ->values()
            ->all();
    }

    private function normalizeStringArray(mixed $value): array
    {
        if ($value instanceof \Illuminate\Support\Collection) {
            $value = $value->all();
        } elseif ($value instanceof \Traversable) {
            $value = iterator_to_array($value);
        }

        if (! is_array($value)) {
            $value = filled($value) ? [$value] : [];
        }

        return array_values(array_filter(
            array_map(static fn($item) => (string) $item, $value),
            static fn($item) => $item !== ''
        ));
    }
}

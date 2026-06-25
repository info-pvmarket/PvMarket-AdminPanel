<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MainMenu;
use App\Models\SubMenu;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Commission;
use App\Services\TranslationService;
use App\Models\ProductListingImage;
use App\Models\Incoterm;
use App\Traits\FiltersAssignedUsers;

class ProductListingController extends Controller
{
    use FiltersAssignedUsers;

    public function __construct(protected TranslationService $translator) {}
    // ── Index (My Listings page) ────────────────────────────────────

    public function index(Request $request)
    {
        $userId = (string) Auth::id();   // ← cast to string to match MongoDB stored value

        //$query = ProductListing::where('user_id', new \MongoDB\BSON\ObjectId(Auth::id()));

        $query = ProductListing::query();

        // Filter by assigned users
        $this->filterByAssignedUsers($query, 'user_id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sku_code', 'like', "%{$search}%");
            });
        }


        $filter        = $request->get('filter', 'all');
        $statusFilter  = $request->get('status_filter', 'all');
        $paymentFilter = $request->get('payment_filter', 'all');
        $warehouseFilter = $request->get('warehouse_id');
        $realTimePriceFilter = $request->get('real_time_price', 'all');

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
            $query->where('warehouse_id', $warehouseFilter);
        }

        //$listings = $query->latest()->paginate(10)->withQueryString();


        $listings = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $unpaidCount = ProductListing::where('user_id', new \MongoDB\BSON\ObjectId(Auth::id()))
            ->where('is_paid', false)
            ->count();

        $warehouses = Warehouse::where('is_active', true)->get();

        $productIds   = $listings->pluck('product_id')->filter()->unique()
            ->map(fn($id) => (string)$id)->values();
        $warehouseIds = $listings->pluck('warehouse_id')->filter()->unique()
            ->map(fn($id) => (string)$id)->values();

        $productsMap   = Product::whereIn('_id', $productIds)->get()
            ->keyBy(fn($p) => (string)$p->_id);

        $warehousesMap = Warehouse::whereIn('_id', $warehouseIds)->get()
            ->keyBy(fn($w) => (string)$w->_id);

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
            'usersMap',
            'imagesMap',
        ));
    }

    // ── Create ──────────────────────────────────────────────────────

    public function create()
    {
        $mainCategories = MainMenu::all();
        $subCategories  = SubMenu::all();
        $products       = Product::all();
        $warehouses     = Warehouse::all();
        $commissions    = Commission::all(['category_id', 'category_name', 'commission_percentage']);

        $sellTypes     = ['sell by pieces', 'sell by containers', 'sell by weight'];
        $currencies    = ['AED', 'USD', 'GBP', 'EUR'];
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
            'is_active'                        => 'nullable|boolean',
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
        $validated['incoterm_id']     = new \MongoDB\BSON\ObjectId($validated['incoterm_id']);
        $validated['slug']            = $request->filled('slug')
            ? \Illuminate\Support\Str::slug($request->slug)
            : \Illuminate\Support\Str::slug($request->product_id . '-' . time());
        $validated['real_time_price'] = $request->boolean('real_time_price', false);
        $validated['is_active']           = $request->boolean('is_active', true);
        $validated['is_paid']             = false;
        $validated['is_sold_off']         = $request->boolean('is_sold_off', false);
        $validated['is_popular']          = $request->boolean('is_popular', false);
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
        $mainCategories = MainMenu::all();
        $subCategories  = SubMenu::all();
        $products       = Product::all();
        $warehouses     = Warehouse::all();

        $mainCategory = MainMenu::where('_id', new \MongoDB\BSON\ObjectId($mainCatId))->first();
        $subCategory  = SubMenu::where('_id', new \MongoDB\BSON\ObjectId($subCatId))->first();
        $product      = Product::where('_id', new \MongoDB\BSON\ObjectId($productId))->first();
        $warehouse    = Warehouse::where('_id', new \MongoDB\BSON\ObjectId($warehouseId))->first();
        $sellTypes     = ['sell by pieces', 'sell by pallets', 'sell by containers', 'sell by weight'];
        $currencies    = ['AED', 'USD', 'GBP', 'EUR'];
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
        ));
    }
    // ── Update ──────────────────────────────────────────────────────

    public function update(Request $request, string $id)
    {
        $listing = ProductListing::findOrFail($id);

        $validated = $request->validate([
            'sell_type'                        => 'required|string',
            'currency_id'                      => 'required|string',
            'discount_type'                    => 'nullable|string',
            'incoterm_id' => 'required|string',
            'slug'        => 'nullable|string|max:255',
            'total_quantity'                   => 'required|integer|min:1',
            'lead_time'                        => 'required|integer|min:0',
            'is_active'                        => 'nullable|boolean',
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
        $validated['is_active']   = $request->boolean('is_active');
        $validated['is_sold_off'] = $request->boolean('is_sold_off', false);
        $validated['is_popular']  = $request->boolean('is_popular', false);
        $validated['incoterm_id']     = new \MongoDB\BSON\ObjectId($request->incoterm_id);
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

        $msg = $listing->is_active ? 'Listing is now active.' : 'Listing is now on hold.';
        return back()->with('success', $msg);
    }

    // ── API: Sub-categories by Main Category ────────────────────
    public function getSubCategories(string $mainCategoryId)
    {
        $subCategories = SubMenu::where('category_id', new \MongoDB\BSON\ObjectId($mainCategoryId))->get();
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
            'verification_status' => 'approved',
            'is_active'           => true,
        ]);

        return back()->with('success', 'Listing approved successfully.');
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
}

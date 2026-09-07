<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\ProductDetailOption;
use App\Models\MainMenu;
use App\Models\SubMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\ProductCsvExporter;
use App\Services\ProductNotificationService;
use App\Services\TranslationService;

class ProductController extends Controller
{
    public function __construct(
        protected TranslationService $translator,
        protected ProductNotificationService $productNotification,
    ) {}
    // ── SKU Generator ─────────────────────────────────
    private function generateSku(string $categoryName): string
    {
        // Remove spaces, take first 3 letters of each word, uppercase
        $words  = preg_split('/\s+/', trim($categoryName));
        $prefix = '';
        foreach ($words as $word) {
            $prefix .= strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $word), 0, 1));
        }
        // Pad or trim to max 3 chars
        $prefix    = substr($prefix, 0, 3);
        $timestamp = now()->format('YmdHis');
        $random    = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

        return "{$prefix}-{$timestamp}-{$random}";
    }

    /**
     * Validate product names case-insensitively against MongoDB.
     */
    private function productNameRules(?string $ignoreId = null): array
    {
        return [
            'bail',
            'required',
            'string',
            'max:500',
            function ($attribute, $value, $fail) use ($ignoreId) {
                $name = trim((string) $value);
                $pattern = '^' . preg_quote($name) . '$';
                $query = Product::query()
                    ->where('product_name', 'regex', new \MongoDB\BSON\Regex($pattern, 'i'));

                if ($ignoreId !== null) {
                    $query->where('_id', '!=', new \MongoDB\BSON\ObjectId($ignoreId));
                }

                if ($query->exists()) {
                    $fail('A product with this name already exists.');
                }
            },
        ];
    }

    // ── Shared dropdown data ──────────────────────────
    private function getDropdowns(mixed $subCategoryId = null, mixed $categoryId = null): array
    {
        $brands    = Brand::where('is_active', true)->orderBy('name')->get();
        $units     = Unit::where('is_active', true)->orderBy('unit_name')->get();
        $mainMenus = MainMenu::availableForDropdown()->orderBy('category_name')->get();

        $categoryObjectId    = $this->toObjectId($categoryId);
        $subCategoryObjectId = $this->toObjectId($subCategoryId);

        // Sub categories cascade from the selected category: with no category
        // picked yet there is nothing to choose from.
        $subMenus = $categoryObjectId
            ? SubMenu::availableForDropdown()
                     ->where('category_id', $categoryObjectId)
                     ->orderBy('sub_category_name')
                     ->get()
            : collect();

        // Keep values already stored on the record selectable even when the
        // category / sub category has since been deactivated or hidden.
        $mainMenus = $this->ensureSelectable($mainMenus, $categoryObjectId, MainMenu::class, 'category_name');
        $subMenus  = $this->ensureSelectable($subMenus, $subCategoryObjectId, SubMenu::class, 'sub_category_name');

        $options = $subCategoryObjectId
            ? ProductDetailOption::where('sub_category_id', $subCategoryObjectId)
                                 ->orderBy('name')
                                 ->get()
            : collect();

        return compact('brands', 'units', 'mainMenus', 'subMenus', 'options');
    }

    /**
     * Make sure the id a form is currently showing exists in its dropdown, so an
     * edit screen never silently drops the value it was opened with.
     */
    private function ensureSelectable($collection, ?\MongoDB\BSON\ObjectId $currentId, string $model, string $sortKey)
    {
        if (!$currentId) {
            return $collection;
        }

        $current = (string) $currentId;
        if ($collection->contains(fn ($item) => (string) $item->_id === $current)) {
            return $collection;
        }

        $missing = $model::find($currentId);

        return $missing
            ? $collection->push($missing)->sortBy($sortKey)->values()
            : $collection;
    }

    /**
     * Build the Product Details rows for the form: every specification defined
     * for the sub category is listed, and only the ones already filled in on the
     * product carry a value - the rest stay blank. Values saved against a
     * specification that no longer exists are kept at the end so editing a
     * product never silently discards them.
     */
    private function buildDetailRows($options, mixed $savedDetails): array
    {
        $saved = [];
        foreach ($this->normalizeList($savedDetails) as $detail) {
            if (is_object($detail) && method_exists($detail, 'getArrayCopy')) {
                $detail = $detail->getArrayCopy();
            }

            if (!is_array($detail)) {
                continue;
            }

            $label = trim((string) ($detail['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $saved[$label] = [
                'value' => (string) ($detail['value'] ?? ''),
                'unit'  => (string) ($detail['unit'] ?? ''),
            ];
        }

        $rows = [];
        foreach ($options as $option) {
            $label = trim((string) $option->name);
            if ($label === '') {
                continue;
            }

            $rows[] = [
                'label'     => $label,
                'value'     => $saved[$label]['value'] ?? '',
                'unit'      => $saved[$label]['unit'] ?? '',
                'units'     => $this->optionUnitNames($option),
                'is_orphan' => false,
            ];

            unset($saved[$label]);
        }

        // Anything left over was saved before the specification list changed.
        foreach ($saved as $label => $detail) {
            $rows[] = [
                'label'     => $label,
                'value'     => $detail['value'],
                'unit'      => $detail['unit'],
                'units'     => [],
                'is_orphan' => true,
            ];
        }

        return $rows;
    }

    /**
     * Turn the submitted Product Details rows into what gets stored. The form
     * always posts every specification of the sub category, so rows the admin
     * left blank are skipped instead of being saved as empty values.
     */
    private function collectSubmittedDetails(mixed $submitted): array
    {
        $details = [];

        foreach ((array) $submitted as $row) {
            if (!is_array($row)) {
                continue;
            }

            $label = trim((string) ($row['label'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));
            $unit  = trim((string) ($row['unit'] ?? ''));

            if ($label === '' || $value === '') {
                continue;
            }

            $details[] = [
                'label' => $label,
                'value' => $value,
                'unit'  => $unit === '' ? null : $unit,
            ];
        }

        return $details;
    }

    /**
     * Unit names offered for a specification, resolved from unit_ids and falling
     * back to the denormalised unit_names.
     */
    private function optionUnitNames($option): array
    {
        $unitIds = collect($this->normalizeList($option->unit_ids ?? []))
            ->map(fn ($id) => $id instanceof \MongoDB\BSON\ObjectId ? $id : $this->toObjectId($id))
            ->filter()
            ->values();

        if ($unitIds->isNotEmpty()) {
            $names = Unit::whereIn('_id', $unitIds->all())
                         ->orderBy('unit_name')
                         ->pluck('unit_name')
                         ->filter()
                         ->unique()
                         ->values()
                         ->all();

            if (!empty($names)) {
                return $names;
            }
        }

        return collect($this->normalizeList($option->unit_names ?? []))
            ->map(fn ($unit) => trim((string) $unit))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function toObjectId(mixed $value): ?\MongoDB\BSON\ObjectId
    {
        if ($value instanceof \MongoDB\BSON\ObjectId) {
            return $value;
        }

        if (is_object($value) && method_exists($value, 'getArrayCopy')) {
            $value = $value->getArrayCopy();
        }

        if (is_array($value)) {
            if (array_key_exists('$oid', $value)) {
                return $this->toObjectId($value['$oid']);
            }

            return count($value) === 1
                ? $this->toObjectId(reset($value))
                : null;
        }

        if (is_object($value)) {
            if (isset($value->{'$oid'})) {
                return $this->toObjectId($value->{'$oid'});
            }

            if (!$value instanceof \Stringable) {
                return null;
            }
        }

        if (!is_scalar($value) && !$value instanceof \Stringable) {
            return null;
        }

        $value = trim((string) $value);

        if ($value !== '' && in_array($value[0], ['[', '{'], true)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->toObjectId($decoded);
            }
        }

        return preg_match('/^[a-f\d]{24}$/i', $value)
            ? new \MongoDB\BSON\ObjectId($value)
            : null;
    }

    /**
     * Normalize native BSON arrays and legacy JSON-encoded array fields.
     */
    private function normalizeList(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_object($value) && method_exists($value, 'getArrayCopy')) {
            $value = $value->getArrayCopy();
        } elseif ($value instanceof \Traversable) {
            $value = iterator_to_array($value);
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return [];
            }

            if (in_array($value[0], ['[', '{'], true)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $this->normalizeList($decoded);
                }
            }

            return [$value];
        }

        if (is_array($value)) {
            if (array_key_exists('$oid', $value)) {
                return [$value];
            }

            $items = [];
            foreach ($value as $item) {
                if (is_string($item) && in_array(substr(ltrim($item), 0, 1), ['[', '{'], true)) {
                    array_push($items, ...$this->normalizeList($item));
                    continue;
                }

                $items[] = $item;
            }

            return $items;
        }

        return [$value];
    }

    // ── Index ─────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Product::query();
        $sort = in_array($request->get('sort'), ['latest', 'oldest'], true)
            ? $request->get('sort')
            : 'latest';

        $categoryFilterId = $this->toObjectId($request->get('category_id'));
        $subCategoryFilterId = $this->toObjectId($request->get('sub_category_id'));
        $categoryFilter = $categoryFilterId ? (string) $categoryFilterId : '';
        $subCategoryFilter = $subCategoryFilterId ? (string) $subCategoryFilterId : '';

        if ($categoryFilterId) {
            $query->where('category_id', $categoryFilterId);
        }

        if ($subCategoryFilterId) {
            $query->where('sub_category_id', $subCategoryFilterId);
        }

        // Verification status filter
        $verificationFilter = $request->get('verification_status', 'all');
        if ($verificationFilter !== 'all') {
            $query->where('verification_status', $verificationFilter);
        }

        
        // Search filter
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('product_name', 'like', "%{$s}%")
                  ->orWhere('brand_name',  'like', "%{$s}%")
                  ->orWhere('sku_code',    'like', "%{$s}%");
            });
        }

        // Listing presence filter
        $listingsFilter = $request->get('listings_filter', 'all');
        if ($listingsFilter !== 'all') {
            $productIdsWithListings = ProductListing::whereNull('deleted_at')
                ->pluck('product_id')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->filter(fn ($id) => preg_match('/^[a-f\d]{24}$/i', $id))
                ->unique()
                ->map(fn ($id) => new \MongoDB\BSON\ObjectId($id))
                ->values()
                ->toArray();

            if ($listingsFilter === 'has_listings') {
                $query->whereIn('_id', $productIdsWithListings);
            } elseif ($listingsFilter === 'no_listings' && !empty($productIdsWithListings)) {
                $query->whereNotIn('_id', $productIdsWithListings);
            }
        }
       

        $products = $query
                  ->orderBy('updated_at', $sort === 'oldest' ? 'asc' : 'desc')
                  ->paginate($request->get('entries', 10))
                  ->withQueryString();

        // Collect all updated_by ObjectIds and resolve to names in one query
        $updatedByIds = $products->pluck('updated_by')
                                 ->filter()
                                 ->map(fn($id) => (string) $id)
                                 ->filter(fn($id) => preg_match('/^[a-f\d]{24}$/i', $id))
                                 ->unique()
                                 ->map(fn($id) => new \MongoDB\BSON\ObjectId($id))
                                 ->values()
                                 ->toArray();

        $userNames = \App\Models\User::whereIn('_id', $updatedByIds)
                                      ->get(['_id', 'name'])
                                      ->mapWithKeys(fn($u) => [(string) $u->_id => $u->name])
                                      ->toArray();

        $createdByIds = $products->pluck('created_by')
                                 ->filter()
                                 ->map(fn($id) => (string) $id)
                                 ->filter(fn($id) => preg_match('/^[a-f\d]{24}$/i', $id))
                                 ->unique()
                                 ->map(fn($id) => new \MongoDB\BSON\ObjectId($id))
                                 ->values()
                                 ->toArray();

        $creatorUsers = \App\Models\User::whereIn('_id', $createdByIds)
                                        ->get(['_id', 'name', 'email', 'mobile', 'phone'])
                                        ->keyBy(fn($u) => (string) $u->_id);

        $filterMainMenus = MainMenu::availableForDropdown()->orderBy('category_name')->get();
        $filterSubMenusQuery = SubMenu::availableForDropdown()->orderBy('sub_category_name');
        if ($categoryFilterId) {
            $filterSubMenusQuery->where('category_id', $categoryFilterId);
        }
        $filterSubMenus = $filterSubMenusQuery->get();


        return view('admin.products.products', [
            'mode'               => 'index',
            'products'           => $products,
            'userNames'          => $userNames,
            'creatorUsers'       => $creatorUsers,
            'verificationFilter' => $verificationFilter,
            'listingsFilter'     => $listingsFilter,
            'categoryFilter'     => $categoryFilter,
            'subCategoryFilter'  => $subCategoryFilter,
            'filterMainMenus'    => $filterMainMenus,
            'filterSubMenus'     => $filterSubMenus,
            'sort'               => $sort,
        ]);
    }

    // ── Export ────────────────────────────────────────
    // Export the complete filtered product result set (not just the current page).
    public function export(Request $request, ProductCsvExporter $exporter)
    {
        $query = Product::query();
        $sort = in_array($request->get('sort'), ['latest', 'oldest'], true)
            ? $request->get('sort')
            : 'latest';

        $categoryFilterId = $this->toObjectId($request->get('category_id'));
        $subCategoryFilterId = $this->toObjectId($request->get('sub_category_id'));

        if ($categoryFilterId) {
            $query->where('category_id', $categoryFilterId);
        }

        if ($subCategoryFilterId) {
            $query->where('sub_category_id', $subCategoryFilterId);
        }

        $verificationFilter = $request->get('verification_status', 'all');
        if ($verificationFilter !== 'all') {
            $query->where('verification_status', $verificationFilter);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                        ->orWhere('brand_name', 'like', "%{$search}%")
                        ->orWhere('sku_code', 'like', "%{$search}%");
                });
            }
        }

        $listingsFilter = $request->get('listings_filter', 'all');
        if ($listingsFilter !== 'all') {
            $productIdsWithListings = ProductListing::whereNull('deleted_at')
                ->pluck('product_id')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->filter(fn ($id) => preg_match('/^[a-f\d]{24}$/i', $id))
                ->unique()
                ->map(fn ($id) => new \MongoDB\BSON\ObjectId($id))
                ->values()
                ->toArray();

            if ($listingsFilter === 'has_listings') {
                $query->whereIn('_id', $productIdsWithListings);
            } elseif ($listingsFilter === 'no_listings' && !empty($productIdsWithListings)) {
                $query->whereNotIn('_id', $productIdsWithListings);
            }
        }

        $products = $query
            ->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc')
            ->get();

        return $exporter->download($products);
    }

    // ── Create ────────────────────────────────────────
    public function create()
    {
        $dropdowns = $this->getDropdowns(old('sub_category_id'), old('category_id'));

        return view('admin.products.products', array_merge(
            [
                'mode'       => 'create',
                'record'     => null,
                'detailRows' => $this->buildDetailRows($dropdowns['options'], old('product_details')),
            ],
            $dropdowns
        ));
    }

    // ── Store ─────────────────────────────────────────
    public function store(Request $request)
    {
        $request->merge([
            'product_name' => trim((string) $request->input('product_name')),
            'specific_value' => trim((string) $request->input('specific_value')),
        ]);

        $request->validate([
            'product_name'   => $this->productNameRules(),
            'category_id'    => 'required|string',
            'sub_category_id'=> 'required|string',
            'brand_id'       => 'nullable|string',
            'specific_value' => 'required|string|max:255',
            'specific_value_unit_id' => ['nullable', 'string', 'regex:/^[a-fA-F0-9]{24}$/'],
            'pieces_per_pallet'    => 'nullable|string|max:100',
            'pallets_per_container'=> 'nullable|string|max:100',
            'product_description'  => 'nullable|string',
        ]);

        // Resolve display names
        $category    = MainMenu::find($request->category_id);
        $subCategory = SubMenu::find($request->sub_category_id);
        $specificValueUnit = $request->filled('specific_value_unit_id')
            ? Unit::findOrFail($request->specific_value_unit_id)
            : null;

        $data = [
    'product_name'          => $request->product_name,
    'product_description'   => $request->product_description,
    'specific_value'        => trim($request->specific_value),
    'specific_value_unit_id'   => $specificValueUnit ? new \MongoDB\BSON\ObjectId((string) $specificValueUnit->_id) : null,
    'specific_value_unit_name' => $specificValueUnit?->unit_name,
    'specific_value_unit_code' => $specificValueUnit?->unit_code,
    'category_id'           => new \MongoDB\BSON\ObjectId($request->category_id),
    'category_name'         => $category?->category_name,
    'sub_category_id'       => new \MongoDB\BSON\ObjectId($request->sub_category_id),
    'sub_category_name'     => $subCategory?->sub_category_name,
    'brand_id'              => $request->brand_id ? new \MongoDB\BSON\ObjectId($request->brand_id) : null,
    'pieces_per_pallet'     => $request->pieces_per_pallet,
    'pallets_per_container' => $request->pallets_per_container,
    'is_popular'            => $request->boolean('is_popular'),
    'real_time_price'       => $request->boolean('real_time_price'),
    'verification_status'   => Auth::user()->role === 'super_admin' ? 'verified' : 'pending',
    'created_by'            => new \MongoDB\BSON\ObjectId(Auth::id()),
    'updated_by'            => new \MongoDB\BSON\ObjectId(Auth::id()),
];

        // Generate SKU using category name
        $data['sku_code'] = $this->generateSku($category?->name ?? 'PRD');

        if ($request->brand_id) {
            $brand = Brand::find($request->brand_id);
            $data['brand_name'] = $brand?->name;
        }

        if ($request->hasFile('datasheet')) {
    $file = $request->file('datasheet');
    $path = $file->store('products/datasheets', 'public');
    $data['datasheet'] = [
        'filename'      => basename($path),
        'original_name' => $file->getClientOriginalName(),
        'path'          => $path,
        'url'           => Storage::disk('public')->url($path),
        'mime_type'     => $file->getClientMimeType(),
        'size'          => $file->getSize(),
        'uploaded_at'   => now()->toISOString(),
    ];
}

        // ── product_details: [{label, value, unit}] ──
        if ($request->has('product_details')) {
            $data['product_details'] = $this->collectSubmittedDetails($request->product_details);
        }

        // ── measurement_details ───────────────────────
        $data['measurement_details'] = [
            'height'      => $request->height      ? (float) $request->height      : null,
            'height_unit' => $request->height_unit ?? null,
            'width'       => $request->width       ? (float) $request->width       : null,
            'width_unit'  => $request->width_unit  ?? null,
            'depth'       => $request->depth       ? (float) $request->depth       : null,
            'depth_unit'  => $request->depth_unit  ?? null,
            'weight'      => $request->weight      ? (float) $request->weight      : null,
            'weight_unit' => $request->weight_unit ?? null,
            
        ];

        $data = $this->attachTranslations($data, new Product());
        $product = Product::create($data);

        $this->productNotification->notifyCreated(
            (string) $product->product_name,
            (string) (Auth::user()?->email ?? 'Unknown user'),
            (bool) (Auth::user()?->isSuperAdmin() ?? false),
        );

        return redirect()->route('admin.products.index')
                         ->with('success', 'Product created successfully.');
    }

    // ── Edit ──────────────────────────────────────────
    public function edit($id)
    {
        $record = Product::findOrFail($id);

        // The sub category list is scoped to the product's category, and the
        // Product Details table is built from that sub category's specifications.
        $dropdowns = $this->getDropdowns(
            old('sub_category_id', $record->sub_category_id),
            old('category_id', $record->category_id),
        );

        return view('admin.products.products', array_merge(
            [
                'mode'       => 'edit',
                'record'     => $record,
                'detailRows' => $this->buildDetailRows(
                    $dropdowns['options'],
                    old('product_details', $record->product_details),
                ),
            ],
            $dropdowns
        ));
    }

    // ── Update ────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->merge([
            'product_name' => trim((string) $request->input('product_name')),
            'specific_value' => trim((string) $request->input('specific_value')),
        ]);

        $request->validate([
            'product_name'    => $this->productNameRules((string) $product->_id),
            'category_id'     => 'required|string',
            'sub_category_id' => 'required|string',
            'specific_value'  => 'required|string|max:255',
            'specific_value_unit_id' => ['nullable', 'string', 'regex:/^[a-fA-F0-9]{24}$/'],
        ]);

        $category    = MainMenu::find($request->category_id);
        $subCategory = SubMenu::find($request->sub_category_id);
        $specificValueUnit = $request->filled('specific_value_unit_id')
            ? Unit::findOrFail($request->specific_value_unit_id)
            : null;

        $data = [
    'product_name'          => $request->product_name,
    'product_description'   => $request->product_description,
    'specific_value'        => trim($request->specific_value),
    'specific_value_unit_id'   => $specificValueUnit ? new \MongoDB\BSON\ObjectId((string) $specificValueUnit->_id) : null,
    'specific_value_unit_name' => $specificValueUnit?->unit_name,
    'specific_value_unit_code' => $specificValueUnit?->unit_code,
    'category_id'           => new \MongoDB\BSON\ObjectId($request->category_id),
    'category_name'         => $category?->category_name,
    'sub_category_id'       => new \MongoDB\BSON\ObjectId($request->sub_category_id),
    'sub_category_name'     => $subCategory?->sub_category_name,
    'brand_id'              => $request->brand_id ? new \MongoDB\BSON\ObjectId($request->brand_id) : null,
    'pieces_per_pallet'     => $request->pieces_per_pallet,
    'pallets_per_container' => $request->pallets_per_container,
    'updated_by'            => new \MongoDB\BSON\ObjectId(Auth::id()),
];

        if ($request->brand_id) {
            $brand = Brand::find($request->brand_id);
            $data['brand_name'] = $brand?->name;
        }

        // ── Datasheet (replace if new file uploaded) ──
        if ($request->hasFile('datasheet')) {
            // Delete old file
            if (!empty($product->datasheet?->path)) {
    Storage::disk('public')->delete($product->datasheet->path);
}
            $file = $request->file('datasheet');
            $path = $file->store('products/datasheets', 'public');
            $data['datasheet'] = [
                'filename'      => basename($path),
                'original_name' => $file->getClientOriginalName(),
                'path'          => $path,
                'url'           => Storage::disk('public')->url($path),
                'mime_type'     => $file->getClientMimeType(),
                'size'          => $file->getSize(),
                'uploaded_at'   => now()->toISOString(),
            ];
        }

        // ── product_details ───────────────────────────
        if ($request->has('product_details')) {
            $data['product_details'] = $this->collectSubmittedDetails($request->product_details);
        }

        // ── measurement_details ───────────────────────
        $data['measurement_details'] = [
            'height'      => $request->height      ? (float) $request->height      : null,
            'height_unit' => $request->height_unit ?? null,
            'width'       => $request->width       ? (float) $request->width       : null,
            'width_unit'  => $request->width_unit  ?? null,
            'depth'       => $request->depth       ? (float) $request->depth       : null,
            'depth_unit'  => $request->depth_unit  ?? null,
            'weight'      => $request->weight      ? (float) $request->weight      : null,
            'weight_unit' => $request->weight_unit ?? null,
            
        ];

        $isSuperAdmin = Auth::user()?->isSuperAdmin() ?? false;
        $data = $this->attachTranslations($data, $product);
        $data = $this->productNotification->requireReapproval($data, $isSuperAdmin);
        $product->update($data);

        $this->productNotification->notifyUpdated(
            (string) $product->product_name,
            (string) (Auth::user()?->email ?? 'Unknown user'),
            $isSuperAdmin,
        );

        return redirect()->route('admin.products.index')
                         ->with('success', 'Product updated.');
    }

    // ── AJAX: Get options by sub_category_id ──────────
    public function getOptionsBySubMenu(Request $request)
    {
        $subCategoryId = $this->toObjectId($request->input('sub_menu_id'));

        if (!$subCategoryId) {
            return response()->json([
                'message' => 'A valid sub category is required.',
                'options' => [],
            ], 422);
        }

        $options = ProductDetailOption::where('sub_category_id', $subCategoryId)
                                      ->orderBy('name')
                                      ->get(['_id', 'name', 'unit_ids', 'unit_names']);

        $options = $options->map(fn ($option) => [
            'option_name' => $option->name,
            // Kept as [{unit_name}] for backwards compatibility with older callers.
            'units'       => collect($this->optionUnitNames($option))
                                ->map(fn ($unitName) => ['unit_name' => $unitName])
                                ->values(),
        ]);

        return response()->json(['options' => $options]);
    }

    // ── AJAX: Get sub categories by category_id ───────
    public function getSubMenusByMainMenu(Request $request)
    {
        $categoryId = $this->toObjectId($request->input('main_menu_id'));

        if (!$categoryId) {
            return response()->json([
                'message'  => 'A valid category is required.',
                'subMenus' => [],
            ], 422);
        }

        $subMenus = SubMenu::availableForDropdown()
            ->where('category_id', $categoryId)
            ->orderBy('sub_category_name')
            ->get(['_id', 'sub_category_name']);

        return response()->json(['subMenus' => $subMenus]);
    }

    // ── Verify ────────────────────────────────────────
    public function verify($id)
    {
        Product::findOrFail($id)->update([
            'verification_status' => 'verified',
            'is_active'            => true,
            'updated_by'          => Auth::user()->name,
        ]);
        return redirect()->route('admin.products.index')->with('success', 'Product verified.');
    }

    // ── Reject ────────────────────────────────────────
    public function reject($id)
    {
        Product::findOrFail($id)->update([
            'verification_status' => 'rejected',
            'updated_by'          => Auth::user()->name,
        ]);
        return redirect()->route('admin.products.index')->with('success', 'Product rejected.');
    }

    // ── Destroy ───────────────────────────────────────
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if (!empty($product->datasheet?->path)) {
    Storage::disk('public')->delete($product->datasheet->path);
}
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
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

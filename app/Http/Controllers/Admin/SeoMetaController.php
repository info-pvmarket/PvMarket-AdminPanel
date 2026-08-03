<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoMetaData;
use App\Models\SeoOGMetaData;
use App\Models\SeoOGImage;
use App\Models\SeoTwitterMetaData;
use App\Models\SeoTwitterImage;
use App\Models\SeoRobotMetaData;
use App\Models\Market;
use App\Models\MainMenu;
use App\Models\SubMenu;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductListing;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SeoMetaController extends Controller
{
    protected TranslationService $translator;

    public function __construct(TranslationService $translator)
    {
        $this->translator = $translator;
    }

    // ─── INDEX ───────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $entries = (int) $request->get('entries', 10);
        $search  = $request->get('search');
        $marketFilter = $request->get('market_id');
        $typeFilter = $request->get('type');

        $query = SeoMetaData::with(['market', 'category', 'subCategory', 'brand', 'product'])
            ->where('is_active', true);

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('meta_title', 'like', "%{$search}%")
                  ->orWhere('meta_description', 'like', "%{$search}%")
                  ->orWhere('page_header', 'like', "%{$search}%");
            });
        }

        // Market filter
        if ($marketFilter) {
            if ($marketFilter === 'global') {
                $query->whereNull('market_id');
            } else {
                $query->where('market_id', $marketFilter);
            }
        }

        // Type filter (based on entity hierarchy)
        if ($typeFilter) {
            switch ($typeFilter) {
                case 'home':
                    $query->whereNull('category_id')
                          ->whereNull('sub_category_id')
                          ->whereNull('brand_id')
                          ->whereNull('product_id');
                    break;
                case 'category':
                    $query->whereNotNull('category_id')
                          ->whereNull('sub_category_id')
                          ->whereNull('brand_id')
                          ->whereNull('product_id');
                    break;
                case 'subcategory':
                    $query->whereNotNull('sub_category_id')
                          ->whereNull('brand_id')
                          ->whereNull('product_id');
                    break;
                case 'brand':
                    $query->whereNotNull('brand_id')
                          ->whereNull('product_id');
                    break;
                case 'product':
                    $query->whereNotNull('product_id');
                    break;
            }
        }

        $records = $query->latest()->paginate($entries)->withQueryString();
        $markets = Market::where('is_active', true)->orderBy('name')->get();

        return view('admin.seo-meta.seo-meta', [
            'mode'    => 'index',
            'records' => $records,
            'markets' => $markets,
        ]);
    }

    // ─── CREATE ──────────────────────────────────────────────────────────────

    public function create()
    {
        $markets     = Market::where('is_active', true)->orderBy('name')->get();
        $categories  = MainMenu::availableForDropdown()->orderBy('category_name')->get();
        $brands      = Brand::where('is_active', true)->orderBy('name')->get();

        return view('admin.seo-meta.seo-meta', [
            'mode'       => 'create',
            'markets'    => $markets,
            'categories' => $categories,
            'brands'     => $brands,
        ]);
    }

    // ─── STORE ───────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'meta_title'       => 'required|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:500',
            'page_header'      => 'nullable|string|max:255',
            'short_description'=> 'nullable|string',
            'bottom_header'    => 'nullable|string|max:255',
            'bottom_description'=> 'nullable|string',
            'canonical_url'    => 'nullable|url|max:500',
            'market_id'        => 'nullable|string',
            'category_id'      => 'nullable|string',
            'sub_category_id'  => 'nullable|string',
            'brand_id'         => 'nullable|string',
        ]);

        // Sanitize ObjectId fields to prevent "undefined" string errors
        $marketId      = $this->sanitizeObjectId($request->market_id);
        $categoryId    = $this->sanitizeObjectId($request->category_id);
        $subCategoryId = $this->sanitizeObjectId($request->sub_category_id);
        $brandId       = $this->sanitizeObjectId($request->brand_id);

        // Check for duplicate entity combination
        $exists = SeoMetaData::forEntity(
            $marketId,
            $categoryId,
            $subCategoryId,
            $brandId,
            null // product_id not used
        )->exists();

        if ($exists) {
            return back()->withErrors('SEO Meta already exists for this entity combination.')
                         ->withInput();
        }

        // Get slugs and market_code from related entities
        $slugs = $this->getEntitySlugs($marketId, $categoryId, $subCategoryId, $brandId);

        // Create main SEO meta
        $seoData = [
            'market_id'         => $marketId,
            'category_id'       => $categoryId,
            'sub_category_id'   => $subCategoryId,
            'brand_id'          => $brandId,
            'market_code'       => $slugs['market_code'],
            'category_slug'     => $slugs['category_slug'],
            'sub_category_slug' => $slugs['sub_category_slug'],
            'brand_slug'        => $slugs['brand_slug'],
            'meta_title'        => $request->meta_title,
            'meta_description'  => $request->meta_description,
            'meta_keywords'     => $request->meta_keywords,
            'page_header'       => $request->page_header,
            'short_description' => $request->short_description,
            'bottom_header'     => $request->bottom_header,
            'bottom_description'=> $request->bottom_description,
            'canonical_url'     => $request->canonical_url,
            'is_active'         => true,
            'created_by'        => Auth::id(),
        ];

        // Remove null values to avoid storing them in MongoDB
        $seoData = array_filter($seoData, fn($value) => $value !== null);

        $seoData = $this->attachTranslations($seoData, new SeoMetaData());
        $seoMeta = SeoMetaData::create($seoData);

        // Create OG Meta
        if ($request->filled('og_title') || $request->filled('og_description')) {
            $ogData = [
                'seo_meta_id'    => $seoMeta->id,
                'og_title'       => $request->og_title ?? $request->meta_title,
                'og_description' => $request->og_description ?? $request->meta_description,
                'og_type'        => $request->og_type ?? 'website',
                'og_url'         => $request->og_url,
                'og_site_name'   => $request->og_site_name ?? 'PV Market',
                'og_locale'      => $request->og_locale ?? 'en_US',
                'is_active'      => true,
                'created_by'     => Auth::id(),
            ];
            $ogData = $this->attachTranslations($ogData, new SeoOGMetaData());
            $ogMeta = SeoOGMetaData::create($ogData);

            // Handle OG images
            $this->handleOgImages($request, $ogMeta->id);
        }

        // Create Twitter Meta
        if ($request->filled('twitter_title') || $request->filled('twitter_description')) {
            $twitterData = [
                'seo_meta_id'         => $seoMeta->id,
                'twitter_card'        => $request->twitter_card ?? 'summary_large_image',
                'twitter_site'        => $request->twitter_site,
                'twitter_creator'     => $request->twitter_creator,
                'twitter_title'       => $request->twitter_title ?? $request->meta_title,
                'twitter_description' => $request->twitter_description ?? $request->meta_description,
                'is_active'           => true,
                'created_by'          => Auth::id(),
            ];
            $twitterData = $this->attachTranslations($twitterData, new SeoTwitterMetaData());
            $twitterMeta = SeoTwitterMetaData::create($twitterData);

            // Handle Twitter images
            $this->handleTwitterImages($request, $twitterMeta->id);
        }

        // Create Robot Meta
        $robotData = [
            'seo_meta_id'       => $seoMeta->id,
            'index'             => $request->boolean('robot_index', true),
            'follow'            => $request->boolean('robot_follow', true),
            'noarchive'         => $request->boolean('robot_noarchive', false),
            'nosnippet'         => $request->boolean('robot_nosnippet', false),
            'noimageindex'      => $request->boolean('robot_noimageindex', false),
            'nocache'           => $request->boolean('robot_nocache', false),
            'max_snippet'       => $request->max_snippet,
            'max_image_preview' => $request->max_image_preview ?? 'large',
            'max_video_preview' => $request->max_video_preview,
            'is_active'         => true,
            'created_by'        => Auth::id(),
        ];
        SeoRobotMetaData::create($robotData);

        return redirect()->route('admin.seo-meta.index')
                         ->with('success', 'SEO Meta created successfully.');
    }

    // ─── EDIT ────────────────────────────────────────────────────────────────

    public function edit(string $id)
    {
        $record = SeoMetaData::with(['ogMeta.images', 'twitterMeta.images', 'robotMeta'])
                             ->findOrFail($id);

        $markets       = Market::where('is_active', true)->orderBy('name')->get();
        $categories    = MainMenu::availableForDropdown()->orderBy('category_name')->get();
        $subCategories = $record->category_id
            ? SubMenu::availableForDropdown()->where('category_id', $record->category_id)->get()
            : collect();
        $brands        = Brand::where('is_active', true)->orderBy('name')->get();

        return view('admin.seo-meta.seo-meta', [
            'mode'          => 'edit',
            'record'        => $record,
            'markets'       => $markets,
            'categories'    => $categories,
            'subCategories' => $subCategories,
            'brands'        => $brands,
        ]);
    }

    // ─── UPDATE ──────────────────────────────────────────────────────────────

    public function update(Request $request, string $id)
    {
        $request->validate([
            'meta_title'       => 'required|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:500',
            'page_header'      => 'nullable|string|max:255',
            'short_description'=> 'nullable|string',
            'bottom_header'    => 'nullable|string|max:255',
            'bottom_description'=> 'nullable|string',
            'canonical_url'    => 'nullable|url|max:500',
        ]);

        $seoMeta = SeoMetaData::findOrFail($id);

        // Update main SEO meta
        $seoData = [
            'meta_title'        => $request->meta_title,
            'meta_description'  => $request->meta_description,
            'meta_keywords'     => $request->meta_keywords,
            'page_header'       => $request->page_header,
            'short_description' => $request->short_description,
            'bottom_header'     => $request->bottom_header,
            'bottom_description'=> $request->bottom_description,
            'canonical_url'     => $request->canonical_url,
            'updated_by'        => Auth::id(),
        ];

        $seoData = $this->attachTranslations($seoData, $seoMeta);
        $seoMeta->update($seoData);

        // Update or create OG Meta
        $ogMeta = $seoMeta->ogMeta;
        if ($request->filled('og_title') || $request->filled('og_description')) {
            $ogData = [
                'seo_meta_id'    => $seoMeta->id,
                'og_title'       => $request->og_title ?? $request->meta_title,
                'og_description' => $request->og_description ?? $request->meta_description,
                'og_type'        => $request->og_type ?? 'website',
                'og_url'         => $request->og_url,
                'og_site_name'   => $request->og_site_name ?? 'PV Market',
                'og_locale'      => $request->og_locale ?? 'en_US',
                'is_active'      => true,
                'updated_by'     => Auth::id(),
            ];
            $ogData = $this->attachTranslations($ogData, $ogMeta ?? new SeoOGMetaData());

            if ($ogMeta) {
                $ogMeta->update($ogData);
            } else {
                $ogData['created_by'] = Auth::id();
                $ogMeta = SeoOGMetaData::create($ogData);
            }

            $this->handleOgImages($request, $ogMeta->id);
        }

        // Update or create Twitter Meta
        $twitterMeta = $seoMeta->twitterMeta;
        if ($request->filled('twitter_title') || $request->filled('twitter_description')) {
            $twitterData = [
                'seo_meta_id'         => $seoMeta->id,
                'twitter_card'        => $request->twitter_card ?? 'summary_large_image',
                'twitter_site'        => $request->twitter_site,
                'twitter_creator'     => $request->twitter_creator,
                'twitter_title'       => $request->twitter_title ?? $request->meta_title,
                'twitter_description' => $request->twitter_description ?? $request->meta_description,
                'is_active'           => true,
                'updated_by'          => Auth::id(),
            ];
            $twitterData = $this->attachTranslations($twitterData, $twitterMeta ?? new SeoTwitterMetaData());

            if ($twitterMeta) {
                $twitterMeta->update($twitterData);
            } else {
                $twitterData['created_by'] = Auth::id();
                $twitterMeta = SeoTwitterMetaData::create($twitterData);
            }

            $this->handleTwitterImages($request, $twitterMeta->id);
        }

        // Update or create Robot Meta
        $robotMeta = $seoMeta->robotMeta;
        $robotData = [
            'seo_meta_id'       => $seoMeta->id,
            'index'             => $request->boolean('robot_index', true),
            'follow'            => $request->boolean('robot_follow', true),
            'noarchive'         => $request->boolean('robot_noarchive', false),
            'nosnippet'         => $request->boolean('robot_nosnippet', false),
            'noimageindex'      => $request->boolean('robot_noimageindex', false),
            'nocache'           => $request->boolean('robot_nocache', false),
            'max_snippet'       => $request->max_snippet,
            'max_image_preview' => $request->max_image_preview ?? 'large',
            'max_video_preview' => $request->max_video_preview,
            'is_active'         => true,
            'updated_by'        => Auth::id(),
        ];

        if ($robotMeta) {
            $robotMeta->update($robotData);
        } else {
            $robotData['created_by'] = Auth::id();
            SeoRobotMetaData::create($robotData);
        }

        return redirect()->route('admin.seo-meta.index')
                         ->with('success', 'SEO Meta updated successfully.');
    }

    // ─── DESTROY ─────────────────────────────────────────────────────────────

    public function destroy(string $id)
    {
        $seoMeta = SeoMetaData::findOrFail($id);

        // Soft delete - set is_active to false
        $seoMeta->update(['is_active' => false]);

        // Also deactivate related records
        if ($seoMeta->ogMeta) {
            $seoMeta->ogMeta->update(['is_active' => false]);
        }
        if ($seoMeta->twitterMeta) {
            $seoMeta->twitterMeta->update(['is_active' => false]);
        }
        if ($seoMeta->robotMeta) {
            $seoMeta->robotMeta->update(['is_active' => false]);
        }

        return redirect()->route('admin.seo-meta.index')
                         ->with('success', 'SEO Meta deleted successfully.');
    }

    // ─── AJAX: GET SUB CATEGORIES ──────────────────────────────────────────────

    public function getSubCategories(Request $request)
    {
        $categoryId = $request->get('category_id');

        if (!$categoryId) {
            return response()->json([]);
        }

        try {
            $objectId = new \MongoDB\BSON\ObjectId($categoryId);
            $subCategories = SubMenu::availableForDropdown()
                               ->where('category_id', $objectId)
                               ->orderBy('sub_category_name')
                               ->get(['_id', 'sub_category_name', 'slug'])
                               ->map(function($item) {
                                   return [
                                       'id' => (string) $item->_id,
                                       'sub_category_name' => $item->sub_category_name,
                                       'slug' => $item->slug,
                                   ];
                               });

            return response()->json($subCategories);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    // ─── AJAX: GET BRANDS ───────────────────────────────────────────────────

    public function getBrands(Request $request)
    {
        $categoryId    = $request->get('category_id');
        $subCategoryId = $request->get('sub_category_id');

        try {
            // If both category and subcategory are selected, filter brands by active listings
            if ($categoryId && $subCategoryId) {
                $categoryObjectId = new \MongoDB\BSON\ObjectId($categoryId);
                $subCategoryObjectId = new \MongoDB\BSON\ObjectId($subCategoryId);

                // Get brand_ids from active listings in this category/subcategory
                $listingQuery = ProductListing::where('is_active', true)
                    ->where('main_category_id', $categoryObjectId)
                    ->where('sub_category_id', $subCategoryObjectId);

                // Get product_ids from listings
                $productIds = $listingQuery->pluck('product_id')->unique()->filter()->toArray();

                if (empty($productIds)) {
                    return response()->json([]);
                }

                // Get brand_ids from products
                $brandIds = Product::whereIn('_id', $productIds)
                    ->pluck('brand_id')
                    ->unique()
                    ->filter()
                    ->toArray();

                if (empty($brandIds)) {
                    return response()->json([]);
                }

                // Return brands with active listings
                $brands = Brand::whereIn('_id', $brandIds)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['_id', 'name', 'slug']);

                return response()->json($this->formatBrands($brands));
            }

            // If only category is selected (no subcategory), filter by category
            if ($categoryId && !$subCategoryId) {
                $categoryObjectId = new \MongoDB\BSON\ObjectId($categoryId);

                $listingQuery = ProductListing::where('is_active', true)
                    ->where('main_category_id', $categoryObjectId);

                $productIds = $listingQuery->pluck('product_id')->unique()->filter()->toArray();

                if (empty($productIds)) {
                    // Return all active brands if no listings found
                    $brands = Brand::where('is_active', true)
                        ->orderBy('name')
                        ->get(['_id', 'name', 'slug']);
                    return response()->json($this->formatBrands($brands));
                }

                $brandIds = Product::whereIn('_id', $productIds)
                    ->pluck('brand_id')
                    ->unique()
                    ->filter()
                    ->toArray();

                if (empty($brandIds)) {
                    $brands = Brand::where('is_active', true)
                        ->orderBy('name')
                        ->get(['_id', 'name', 'slug']);
                    return response()->json($this->formatBrands($brands));
                }

                $brands = Brand::whereIn('_id', $brandIds)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['_id', 'name', 'slug']);

                return response()->json($this->formatBrands($brands));
            }
        } catch (\Exception $e) {
            // If ObjectId conversion fails, return all brands
        }

        // If no category/subcategory selected, return all active brands
        $brands = Brand::where('is_active', true)
            ->orderBy('name')
            ->get(['_id', 'name', 'slug']);

        return response()->json($this->formatBrands($brands));
    }

    // ─── PRIVATE: Format Brands for JSON ────────────────────────────────────────

    private function formatBrands($brands)
    {
        return $brands->map(function($item) {
            return [
                'id' => (string) $item->_id,
                'name' => $item->name,
                'slug' => $item->slug,
            ];
        });
    }

    // ─── PRIVATE: Handle OG Images ───────────────────────────────────────────

    private function handleOgImages(Request $request, string $ogMetaId): void
    {
        // Handle new image uploads
        if ($request->hasFile('og_images')) {
            foreach ($request->file('og_images') as $index => $file) {
                $filename = time() . '_og_' . $index . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('uploads/seo/og', $filename, 'public');

                SeoOGImage::create([
                    'og_meta_id'   => $ogMetaId,
                    'image_url'    => $path,
                    'image_alt'    => $request->input("og_images_alt.{$index}"),
                    'image_width'  => 1200,
                    'image_height' => 630,
                    'sort_order'   => $index,
                    'is_active'    => true,
                ]);
            }
        }

        // Handle image deletions
        if ($request->has('delete_og_images')) {
            SeoOGImage::whereIn('_id', $request->delete_og_images)->delete();
        }
    }

    // ─── PRIVATE: Handle Twitter Images ──────────────────────────────────────

    private function handleTwitterImages(Request $request, string $twitterMetaId): void
    {
        // Handle new image uploads
        if ($request->hasFile('twitter_images')) {
            foreach ($request->file('twitter_images') as $index => $file) {
                $filename = time() . '_twitter_' . $index . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('uploads/seo/twitter', $filename, 'public');

                SeoTwitterImage::create([
                    'twitter_meta_id' => $twitterMetaId,
                    'image_url'       => $path,
                    'image_alt'       => $request->input("twitter_images_alt.{$index}"),
                    'sort_order'      => $index,
                    'is_active'       => true,
                ]);
            }
        }

        // Handle image deletions
        if ($request->has('delete_twitter_images')) {
            SeoTwitterImage::whereIn('_id', $request->delete_twitter_images)->delete();
        }
    }

    // ─── PRIVATE: Attach Translations ────────────────────────────────────────
    // NOTE: Automatic translations disabled for SEO meta to avoid timeout.
    // Translations are done on-demand via the trans() method from HasTranslations trait.

    private function attachTranslations(array $data, $modelInstance): array
    {
        // Skip automatic translations - they will be done on-demand
        // when the trans() method is called on the model
        return $data;
    }

    // ─── PRIVATE: Sanitize ObjectId ─────────────────────────────────────────────
    // Ensures ObjectId fields are properly null when empty, "undefined", or "null"

    private function sanitizeObjectId($value): ?string
    {
        if (empty($value) || $value === 'undefined' || $value === 'null' || $value === '') {
            return null;
        }
        return $value;
    }

    // ─── PRIVATE: Get Entity Slugs ─────────────────────────────────────────────
    // Returns all entity slugs and market code in lowercase

    private function getEntitySlugs(?string $marketId, ?string $categoryId, ?string $subCategoryId, ?string $brandId): array
    {
        $slugs = [
            'market_code'       => null,
            'category_slug'     => null,
            'sub_category_slug' => null,
            'brand_slug'        => null,
        ];

        if ($marketId) {
            $market = Market::find($marketId);
            if ($market && $market->code) {
                $slugs['market_code'] = strtolower($market->code);
            }
        }

        if ($categoryId) {
            $category = MainMenu::find($categoryId);
            if ($category && $category->slug) {
                $slugs['category_slug'] = strtolower($category->slug);
            }
        }

        if ($subCategoryId) {
            $subCategory = SubMenu::find($subCategoryId);
            if ($subCategory && $subCategory->slug) {
                $slugs['sub_category_slug'] = strtolower($subCategory->slug);
            }
        }

        if ($brandId) {
            $brand = Brand::find($brandId);
            if ($brand && $brand->slug) {
                $slugs['brand_slug'] = strtolower($brand->slug);
            }
        }

        return $slugs;
    }
}

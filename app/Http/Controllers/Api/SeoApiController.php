<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SeoMetaData;
use App\Models\Market;
use App\Models\MainMenu;
use App\Models\SubMenu;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SeoApiController extends Controller
{
    /**
     * Get SEO Meta data for a specific page
     *
     * Supports both ID-based and slug-based queries:
     *
     * By IDs:
     * GET /api/seo-meta?market_id=xxx&category_id=xxx&sub_category_id=xxx&brand_id=xxx&product_id=xxx
     *
     * By Slugs/Codes:
     * GET /api/seo-meta?market_code=in&category_slug=pv-modules&subcategory_slug=mono&brand_slug=jinko
     *
     * URL Pattern Examples:
     * - pv.market/             → No params (Global Home)
     * - pv.market/in           → market_code=in (Market Home)
     * - pv.market/pv-modules   → category_slug=pv-modules (Global + Category)
     * - pv.market/in/pv-modules → market_code=in&category_slug=pv-modules (Market + Category)
     */
    public function getSeoMeta(Request $request): JsonResponse
    {
        $locale = $request->header('Accept-Language', 'en');

        // Resolve IDs from slugs if provided
        $marketId      = $this->resolveMarketId($request);
        $categoryId    = $this->resolveCategoryId($request);
        $subCategoryId = $this->resolveSubCategoryId($request);
        $brandId       = $this->resolveBrandId($request);
        $productId     = $this->resolveProductId($request);

        // Try to find exact match first
        $seoMeta = SeoMetaData::with(['ogMeta.images', 'twitterMeta.images', 'robotMeta'])
            ->forEntity($marketId, $categoryId, $subCategoryId, $brandId, $productId)
            ->where('is_active', true)
            ->first();

        // Fallback strategy: Try without market (global)
        if (!$seoMeta && $marketId) {
            $seoMeta = SeoMetaData::with(['ogMeta.images', 'twitterMeta.images', 'robotMeta'])
                ->forEntity(null, $categoryId, $subCategoryId, $brandId, $productId)
                ->where('is_active', true)
                ->first();
        }

        // Further fallback: Try parent level
        if (!$seoMeta && $productId) {
            // Try brand level
            $seoMeta = $this->findFallback($marketId, $categoryId, $subCategoryId, $brandId, null);
        }

        if (!$seoMeta && $brandId) {
            // Try subcategory level
            $seoMeta = $this->findFallback($marketId, $categoryId, $subCategoryId, null, null);
        }

        if (!$seoMeta && $subCategoryId) {
            // Try category level
            $seoMeta = $this->findFallback($marketId, $categoryId, null, null, null);
        }

        if (!$seoMeta && $categoryId) {
            // Try home level for market
            $seoMeta = $this->findFallback($marketId, null, null, null, null);
        }

        if (!$seoMeta) {
            return response()->json([
                'success' => false,
                'message' => 'SEO Meta not found',
                'data'    => $this->getDefaultSeoMeta(),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatSeoMeta($seoMeta, $locale),
        ]);
    }

    /**
     * Get all SEO meta entries for sitemap generation
     */
    public function getAllForSitemap(Request $request): JsonResponse
    {
        $marketCode = $request->get('market_code');

        $query = SeoMetaData::with(['market', 'category', 'subCategory', 'brand', 'product'])
            ->where('is_active', true);

        if ($marketCode) {
            $market = Market::where('code', $marketCode)->first();
            if ($market) {
                $query->where('market_id', $market->id);
            }
        }

        $entries = $query->get();

        $urls = $entries->map(function ($entry) {
            return [
                'url'          => $this->buildUrlPath($entry),
                'lastmod'      => $entry->updated_at->toIso8601String(),
                'changefreq'   => $this->getChangeFreq($entry),
                'priority'     => $this->getPriority($entry),
                'page_type'    => $entry->page_type,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $urls,
        ]);
    }

    // ─── PRIVATE: Resolve Market ID ──────────────────────────────────────────

    private function resolveMarketId(Request $request): ?string
    {
        if ($request->filled('market_id')) {
            return $request->market_id;
        }

        if ($request->filled('market_code')) {
            $market = Market::where('code', $request->market_code)->first();
            return $market?->id;
        }

        return null;
    }

    // ─── PRIVATE: Resolve Category ID ──────────────────────────────────────────

    private function resolveCategoryId(Request $request): ?string
    {
        if ($request->filled('category_id')) {
            return $request->category_id;
        }

        if ($request->filled('category_slug')) {
            $category = MainMenu::where('slug', $request->category_slug)->first();
            return $category?->id;
        }

        return null;
    }

    // ─── PRIVATE: Resolve SubCategory ID ────────────────────────────────────────

    private function resolveSubCategoryId(Request $request): ?string
    {
        if ($request->filled('sub_category_id')) {
            return $request->sub_category_id;
        }

        if ($request->filled('subcategory_slug')) {
            $subCategory = SubMenu::where('slug', $request->subcategory_slug)->first();
            return $subCategory?->id;
        }

        return null;
    }

    // ─── PRIVATE: Resolve Brand ID ───────────────────────────────────────────

    private function resolveBrandId(Request $request): ?string
    {
        if ($request->filled('brand_id')) {
            return $request->brand_id;
        }

        if ($request->filled('brand_slug')) {
            $brand = Brand::where('slug', $request->brand_slug)->first();
            return $brand?->id;
        }

        return null;
    }

    // ─── PRIVATE: Resolve Product ID ─────────────────────────────────────────

    private function resolveProductId(Request $request): ?string
    {
        if ($request->filled('product_id')) {
            return $request->product_id;
        }

        if ($request->filled('product_slug')) {
            $product = Product::where('sku_code', $request->product_slug)->first();
            return $product?->id;
        }

        return null;
    }

    // ─── PRIVATE: Find Fallback ──────────────────────────────────────────────

    private function findFallback($marketId, $categoryId, $subCategoryId, $brandId, $productId): ?SeoMetaData
    {
        // Try with market
        $seoMeta = SeoMetaData::with(['ogMeta.images', 'twitterMeta.images', 'robotMeta'])
            ->forEntity($marketId, $categoryId, $subCategoryId, $brandId, $productId)
            ->where('is_active', true)
            ->first();

        // Try without market (global)
        if (!$seoMeta && $marketId) {
            $seoMeta = SeoMetaData::with(['ogMeta.images', 'twitterMeta.images', 'robotMeta'])
                ->forEntity(null, $categoryId, $subCategoryId, $brandId, $productId)
                ->where('is_active', true)
                ->first();
        }

        return $seoMeta;
    }

    // ─── PRIVATE: Format SEO Meta Response ───────────────────────────────────

    private function formatSeoMeta(SeoMetaData $seoMeta, string $locale = 'en'): array
    {
        $ogMeta      = $seoMeta->ogMeta;
        $twitterMeta = $seoMeta->twitterMeta;
        $robotMeta   = $seoMeta->robotMeta;

        return [
            'id'         => $seoMeta->id,
            'page_type'  => $seoMeta->page_type,

            // Basic Meta
            'meta' => [
                'title'       => $seoMeta->trans('meta_title', $locale),
                'description' => $seoMeta->trans('meta_description', $locale),
                'keywords'    => $seoMeta->trans('meta_keywords', $locale),
                'canonical'   => $seoMeta->canonical_url,
            ],

            // Page Content
            'content' => [
                'page_header'        => $seoMeta->trans('page_header', $locale),
                'short_description'  => $seoMeta->trans('short_description', $locale),
                'content'            => $seoMeta->trans('content', $locale),
                'bottom_header'      => $seoMeta->trans('bottom_header', $locale),
                'bottom_description' => $seoMeta->trans('bottom_description', $locale),
            ],

            // Open Graph
            'og' => $ogMeta ? [
                'title'       => $ogMeta->trans('og_title', $locale),
                'description' => $ogMeta->trans('og_description', $locale),
                'type'        => $ogMeta->og_type,
                'url'         => $ogMeta->og_url,
                'site_name'   => $ogMeta->og_site_name,
                'locale'      => $ogMeta->og_locale,
                'images'      => $ogMeta->images->where('is_active', true)->map(fn($img) => [
                    'url'    => asset('storage/' . $img->image_url),
                    'alt'    => $img->image_alt,
                    'width'  => $img->image_width,
                    'height' => $img->image_height,
                ])->values(),
            ] : null,

            // Twitter Card
            'twitter' => $twitterMeta ? [
                'card'        => $twitterMeta->twitter_card,
                'site'        => $twitterMeta->twitter_site,
                'creator'     => $twitterMeta->twitter_creator,
                'title'       => $twitterMeta->trans('twitter_title', $locale),
                'description' => $twitterMeta->trans('twitter_description', $locale),
                'images'      => $twitterMeta->images->where('is_active', true)->map(fn($img) => [
                    'url' => asset('storage/' . $img->image_url),
                    'alt' => $img->image_alt,
                ])->values(),
            ] : null,

            // Robots
            'robots' => $robotMeta ? [
                'content'           => $robotMeta->robots_content,
                'index'             => $robotMeta->index,
                'follow'            => $robotMeta->follow,
                'max_snippet'       => $robotMeta->max_snippet,
                'max_image_preview' => $robotMeta->max_image_preview,
                'max_video_preview' => $robotMeta->max_video_preview,
            ] : [
                'content' => 'index, follow',
                'index'   => true,
                'follow'  => true,
            ],

            // Entity References
            'entity' => [
                'market_id'       => $seoMeta->market_id,
                'category_id'     => $seoMeta->category_id,
                'sub_category_id' => $seoMeta->sub_category_id,
                'brand_id'        => $seoMeta->brand_id,
                'product_id'      => $seoMeta->product_id,
            ],
        ];
    }

    // ─── PRIVATE: Get Default SEO Meta ───────────────────────────────────────

    private function getDefaultSeoMeta(): array
    {
        return [
            'meta' => [
                'title'       => 'PV Market - Solar Energy Marketplace',
                'description' => 'Buy and sell solar panels, inverters, and energy storage solutions.',
                'keywords'    => 'solar panels, pv modules, inverters, energy storage',
                'canonical'   => null,
            ],
            'content' => [
                'page_header'        => null,
                'short_description'  => null,
                'content'            => null,
                'bottom_header'      => null,
                'bottom_description' => null,
            ],
            'og'      => null,
            'twitter' => null,
            'robots'  => [
                'content' => 'index, follow',
                'index'   => true,
                'follow'  => true,
            ],
            'entity' => [
                'market_id'       => null,
                'category_id'     => null,
                'sub_category_id' => null,
                'brand_id'        => null,
                'product_id'      => null,
            ],
        ];
    }

    // ─── PRIVATE: Build URL Path ─────────────────────────────────────────────

    private function buildUrlPath(SeoMetaData $entry): string
    {
        $parts = [];

        // Add market code
        if ($entry->market) {
            $parts[] = $entry->market->code;
        }

        // Add category slug
        if ($entry->category) {
            $parts[] = $entry->category->slug;
        }

        // Add subcategory slug
        if ($entry->subCategory) {
            $parts[] = $entry->subCategory->slug;
        }

        // Add brand slug
        if ($entry->brand) {
            $parts[] = $entry->brand->slug;
        }

        // Add product SKU
        if ($entry->product) {
            $parts[] = $entry->product->sku_code;
        }

        return '/' . implode('/', $parts);
    }

    // ─── PRIVATE: Get Change Frequency ───────────────────────────────────────

    private function getChangeFreq(SeoMetaData $entry): string
    {
        if ($entry->product_id) return 'weekly';
        if ($entry->brand_id) return 'weekly';
        if ($entry->sub_category_id) return 'weekly';
        if ($entry->category_id) return 'daily';
        return 'daily'; // Home pages
    }

    // ─── PRIVATE: Get Priority ───────────────────────────────────────────────

    private function getPriority(SeoMetaData $entry): float
    {
        if (!$entry->category_id) return 1.0; // Home
        if (!$entry->sub_category_id) return 0.9;  // Category
        if (!$entry->brand_id && !$entry->product_id) return 0.8; // Subcategory
        if ($entry->brand_id && !$entry->product_id) return 0.7; // Brand
        return 0.6; // Product
    }
}

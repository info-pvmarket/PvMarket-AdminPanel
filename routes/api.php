<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SeoApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// ══════════════════════════════════════════════════════════════════════════
// SEO META API
// ══════════════════════════════════════════════════════════════════════════

/**
 * Get SEO Meta for a specific page
 *
 * Supports both ID-based and slug-based queries:
 *
 * By IDs:
 * GET /api/seo-meta?location_id=xxx&main_menu_id=xxx&sub_menu_id=xxx&brand_id=xxx&product_id=xxx
 *
 * By Slugs:
 * GET /api/seo-meta?location_slug=in&category_slug=pv-modules&subcategory_slug=mono&brand_slug=jinko
 *
 * URL Pattern Examples:
 * - pv.market/             → No params (Global Home)
 * - pv.market/in           → market_code=in (Market Home)
 * - pv.market/pv-modules   → category_slug=pv-modules (Global + Category)
 * - pv.market/in/pv-modules → market_code=in&category_slug=pv-modules (Market + Category)
 *
 * Headers:
 * - Accept-Language: en (default) | ar | es | de | etc.
 */
Route::get('seo-meta', [SeoApiController::class, 'getSeoMeta']);

/**
 * Get all SEO Meta entries for sitemap generation
 *
 * GET /api/seo-meta/sitemap?market_code=in
 *
 * Returns array of URLs with lastmod, changefreq, priority for XML sitemap
 */
Route::get('seo-meta/sitemap', [SeoApiController::class, 'getAllForSitemap']);

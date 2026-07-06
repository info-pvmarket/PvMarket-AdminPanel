<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SeoApiController;
use App\Http\Controllers\Api\SolarAnalysisController;

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

Route::prefix('solar-analysis')->group(function () {
    Route::post('analyze', [SolarAnalysisController::class, 'analyze']);
    Route::get('products', [SolarAnalysisController::class, 'products']);
    Route::post('save', [SolarAnalysisController::class, 'save']);
    Route::get('projects', [SolarAnalysisController::class, 'projects']);
    Route::get('projects/{project}', [SolarAnalysisController::class, 'show']);
    Route::post('projects/{project}/submit', [SolarAnalysisController::class, 'submit']);
    Route::get('marketplace', [SolarAnalysisController::class, 'marketplace']);
    Route::get('marketplace/{project}', [SolarAnalysisController::class, 'marketplaceShow']);
    Route::post('quotes/submit', [SolarAnalysisController::class, 'submitQuote']);
    Route::get('projects/{project}/quotes', [SolarAnalysisController::class, 'quotes']);
    Route::get('projects/{project}/pdf', [SolarAnalysisController::class, 'pdf']);
    Route::get('projects/{project}/pdf-base64', [SolarAnalysisController::class, 'pdfBase64']);
});

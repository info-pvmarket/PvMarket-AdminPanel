<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\AdvertisementController;
use App\Http\Controllers\Admin\IncotermController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\SubAdminController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\ChargeController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\PricePromotionController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\MainMenuController;
use App\Http\Controllers\Admin\SubMenuController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\ProductDetailOptionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PvSpotPriceController;
use App\Http\Controllers\Admin\PageSectionController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\BidRequestController;
use App\Http\Controllers\Admin\ProductListingController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\RfqRequestController;
use App\Http\Controllers\Admin\MarketController;

// ── Redirect root to login ──────────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('admin.login.page');
});

// ── Guest only routes ───────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login.page');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login');
});

// ── Protected routes ────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard (accessible to all authenticated admin users)
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Profile (accessible to all authenticated users)
    Route::get('/admin/profile', [ProfileController::class, 'show'])->name('admin.profile');
    Route::put('/admin/profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::put('/admin/profile/password', [ProfileController::class, 'updatePassword'])->name('admin.profile.password');

    // Logout
    Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

    // ══════════════════════════════════════════════════════════════════════
    // SETTINGS - Categories
    // ══════════════════════════════════════════════════════════════════════
    Route::middleware('admin.permission:settings.categories')->group(function () {
        Route::get('/setup/main-menus', [MainMenuController::class, 'index'])->name('admin.setup.main-menus.index');
        Route::get('/setup/main-menus/create', [MainMenuController::class, 'create'])->name('admin.setup.main-menus.create');
        Route::post('/setup/main-menus', [MainMenuController::class, 'store'])->name('admin.setup.main-menus.store');
        Route::get('/setup/main-menus/{id}/edit', [MainMenuController::class, 'edit'])->name('admin.setup.main-menus.edit');
        Route::put('/setup/main-menus/{id}', [MainMenuController::class, 'update'])->name('admin.setup.main-menus.update');
        Route::patch('/setup/main-menus/{id}/toggle', [MainMenuController::class, 'toggleStatus'])->name('admin.setup.main-menus.toggle');
        Route::delete('/setup/main-menus/{id}', [MainMenuController::class, 'destroy'])->name('admin.setup.main-menus.destroy');
        Route::patch('main-menus/{id}/stock-toggle', [MainMenuController::class, 'toggleStock'])->name('admin.setup.main-menus.stock-toggle');
        Route::post('/setup/main-menus/reorder', [MainMenuController::class, 'reorder'])->name('admin.setup.main-menus.reorder');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SETTINGS - Sub Categories
    // ══════════════════════════════════════════════════════════════════════
    Route::middleware('admin.permission:settings.sub_categories')->group(function () {
        Route::get('/setup/sub-menus', [SubMenuController::class, 'index'])->name('admin.setup.sub-menus.index');
        Route::get('/setup/sub-menus/create', [SubMenuController::class, 'create'])->name('admin.setup.sub-menus.create');
        Route::post('/setup/sub-menus', [SubMenuController::class, 'store'])->name('admin.setup.sub-menus.store');
        Route::get('/setup/sub-menus/{id}/edit', [SubMenuController::class, 'edit'])->name('admin.setup.sub-menus.edit');
        Route::put('/setup/sub-menus/{id}', [SubMenuController::class, 'update'])->name('admin.setup.sub-menus.update');
        Route::patch('/setup/sub-menus/{id}/toggle', [SubMenuController::class, 'toggleStatus'])->name('admin.setup.sub-menus.toggle');
        Route::delete('/setup/sub-menus/{id}', [SubMenuController::class, 'destroy'])->name('admin.setup.sub-menus.destroy');
        Route::patch('sub-menus/{id}/stock-toggle', [SubMenuController::class, 'toggleStock'])->name('admin.setup.sub-menus.stock-toggle');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SETTINGS - Brands
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/setup/brands')->name('admin.setup.brands.')->middleware('admin.permission:settings.brands')->group(function () {
        Route::get('/', [BrandController::class, 'index'])->name('index');
        Route::get('/create', [BrandController::class, 'create'])->name('create');
        Route::post('/', [BrandController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [BrandController::class, 'edit'])->name('edit');
        Route::put('/{id}', [BrandController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle', [BrandController::class, 'toggle'])->name('toggle');
        Route::delete('/{id}', [BrandController::class, 'destroy'])->name('destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SETTINGS - Units
    // ══════════════════════════════════════════════════════════════════════
    Route::middleware('admin.permission:settings.units')->group(function () {
        Route::get('/setup/units', [UnitController::class, 'index'])->name('admin.setup.units.index');
        Route::get('/setup/units/create', [UnitController::class, 'create'])->name('admin.setup.units.create');
        Route::post('/setup/units', [UnitController::class, 'store'])->name('admin.setup.units.store');
        Route::get('/setup/units/{id}/edit', [UnitController::class, 'edit'])->name('admin.setup.units.edit');
        Route::put('/setup/units/{id}', [UnitController::class, 'update'])->name('admin.setup.units.update');
        Route::patch('/setup/units/{id}/toggle', [UnitController::class, 'toggleStatus'])->name('admin.setup.units.toggle');
        Route::delete('/setup/units/{id}', [UnitController::class, 'destroy'])->name('admin.setup.units.destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SETTINGS - Locations
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/setup/locations')->name('admin.setup.locations.')->middleware('admin.permission:settings.locations')->group(function () {
        Route::get('/', [LocationController::class, 'index'])->name('index');
        Route::get('/create', [LocationController::class, 'create'])->name('create');
        Route::post('/', [LocationController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [LocationController::class, 'edit'])->name('edit');
        Route::put('/{id}', [LocationController::class, 'update'])->name('update');
        Route::delete('/{id}', [LocationController::class, 'destroy'])->name('destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SETTINGS - Sliders
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/setup/sliders')->name('admin.setup.sliders.')->middleware('admin.permission:settings.sliders')->group(function () {
        Route::get('/', [SliderController::class, 'index'])->name('index');
        Route::get('/create', [SliderController::class, 'create'])->name('create');
        Route::post('/', [SliderController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [SliderController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SliderController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle', [SliderController::class, 'toggle'])->name('toggle');
        Route::delete('/{id}', [SliderController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [SliderController::class, 'reorder'])->name('reorder');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SETTINGS - Advertisements
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/setup/advertisements')->name('admin.setup.advertisements.')->middleware('admin.permission:settings.advertisements')->group(function () {
        Route::get('/', [AdvertisementController::class, 'index'])->name('index');
        Route::get('/create', [AdvertisementController::class, 'create'])->name('create');
        Route::post('/', [AdvertisementController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdvertisementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdvertisementController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle', [AdvertisementController::class, 'toggle'])->name('toggle');
        Route::delete('/{id}', [AdvertisementController::class, 'destroy'])->name('destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SETTINGS - Charges
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/setup/charges')->name('admin.setup.charges.')->middleware('admin.permission:settings.charges')->group(function () {
        Route::get('/', [ChargeController::class, 'index'])->name('index');
        Route::get('/create', [ChargeController::class, 'create'])->name('create');
        Route::post('/', [ChargeController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [ChargeController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ChargeController::class, 'update'])->name('update');
        Route::delete('/{id}', [ChargeController::class, 'destroy'])->name('destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SETTINGS - Commissions
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('setup/commissions')->name('admin.setup.commissions.')->middleware('admin.permission:settings.commissions')->group(function () {
        Route::get('/', [CommissionController::class, 'index'])->name('index');
        Route::get('/create', [CommissionController::class, 'create'])->name('create');
        Route::post('/', [CommissionController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CommissionController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CommissionController::class, 'update'])->name('update');
        Route::delete('/{id}', [CommissionController::class, 'destroy'])->name('destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SETTINGS - Sub Admins
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/setup/sub-admins')->name('admin.setup.sub-admins.')->middleware('admin.permission:settings.sub_admins')->group(function () {
        Route::get('/export', [SubAdminController::class, 'export'])->name('export');
        Route::get('/', [SubAdminController::class, 'index'])->name('index');
        Route::get('/create', [SubAdminController::class, 'create'])->name('create');
        Route::post('/', [SubAdminController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [SubAdminController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SubAdminController::class, 'update'])->name('update');
        Route::delete('/{id}', [SubAdminController::class, 'destroy'])->name('destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SETTINGS - Roles
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/setup/roles')->name('admin.setup.roles.')->middleware('admin.permission:settings.roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{id}', [RoleController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle', [RoleController::class, 'toggle'])->name('toggle');
        Route::delete('/{id}', [RoleController::class, 'destroy'])->name('destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SETTINGS - Countries
    // ══════════════════════════════════════════════════════════════════════
    Route::middleware('admin.permission:settings.countries')->group(function () {
        Route::post('admin/setup/countries/import-api', [CountryController::class, 'importFromApi'])->name('admin.setup.countries.import-api');
        Route::get('admin/setup/countries', [CountryController::class, 'index'])->name('admin.setup.countries.index');
        Route::get('admin/setup/countries/create', [CountryController::class, 'create'])->name('admin.setup.countries.create');
        Route::post('admin/setup/countries', [CountryController::class, 'store'])->name('admin.setup.countries.store');
        Route::get('admin/setup/countries/{id}/edit', [CountryController::class, 'edit'])->name('admin.setup.countries.edit');
        Route::put('admin/setup/countries/{id}', [CountryController::class, 'update'])->name('admin.setup.countries.update');
        Route::delete('admin/setup/countries/{id}', [CountryController::class, 'destroy'])->name('admin.setup.countries.destroy');
        Route::patch('admin/setup/countries/{id}/default', [CountryController::class, 'setDefault'])->name('admin.setup.countries.default');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SETTINGS - Coupons
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/setup/coupons')->name('admin.setup.coupons.')->middleware('admin.permission:settings.coupons')->group(function () {
        Route::get('/', [CouponController::class, 'index'])->name('index');
        Route::get('/create', [CouponController::class, 'create'])->name('create');
        Route::post('/', [CouponController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CouponController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CouponController::class, 'update'])->name('update');
        Route::delete('/{id}', [CouponController::class, 'destroy'])->name('destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SETTINGS - Incoterms
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/setup/incoterms')->name('admin.setup.incoterms.')->middleware('admin.permission:settings.incoterms')->group(function () {
        Route::get('/', [IncotermController::class, 'index'])->name('index');
        Route::get('/create', [IncotermController::class, 'create'])->name('create');
        Route::post('/', [IncotermController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [IncotermController::class, 'edit'])->name('edit');
        Route::put('/{id}', [IncotermController::class, 'update'])->name('update');
        Route::delete('/{id}', [IncotermController::class, 'destroy'])->name('destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SETTINGS - Languages
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/setup')->name('admin.setup.')->middleware('admin.permission:settings.languages')->group(function () {
        Route::get('languages', [LanguageController::class, 'index'])->name('languages.index');
        Route::post('languages', [LanguageController::class, 'store'])->name('languages.store');
        Route::post('languages/set-default', [LanguageController::class, 'setDefault'])->name('languages.set-default');
        Route::post('languages/{code}/translate', [LanguageController::class, 'translate'])->name('languages.translate');
        Route::put('languages/{code}', [LanguageController::class, 'update'])->name('languages.update');
        Route::delete('languages/{code}', [LanguageController::class, 'destroy'])->name('languages.destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SETTINGS - Markets
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/setup/markets')->name('admin.setup.markets.')->middleware('admin.permission:settings.markets')->group(function () {
        Route::get('/', [MarketController::class, 'index'])->name('index');
        Route::get('/create', [MarketController::class, 'create'])->name('create');
        Route::post('/', [MarketController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [MarketController::class, 'edit'])->name('edit');
        Route::put('/{id}', [MarketController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle', [MarketController::class, 'toggle'])->name('toggle');
        Route::delete('/{id}', [MarketController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/domains', [MarketController::class, 'addDomain'])->name('domains.add');
        Route::delete('/{id}/domains/{domainId}', [MarketController::class, 'removeDomain'])->name('domains.remove');
        Route::patch('/{id}/domains/{domainId}/primary', [MarketController::class, 'setPrimaryDomain'])->name('domains.primary');
    });

    // ══════════════════════════════════════════════════════════════════════
    // STATIC PAGES
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/page-sections')->name('admin.page-sections.')->middleware('admin.permission:static_pages')->group(function () {
        Route::get('/', [PageSectionController::class, 'index'])->name('index');
        Route::get('/{market}', [PageSectionController::class, 'pages'])->name('pages');
        Route::get('/{market}/{page}', [PageSectionController::class, 'edit'])->name('edit');
        Route::put('/{market}/{page}', [PageSectionController::class, 'update'])->name('update');
        Route::post('/{market}/copy-from', [PageSectionController::class, 'copyFrom'])->name('copy-from');
    });

    // ══════════════════════════════════════════════════════════════════════
    // KNOWLEDGE HUB - News
    // ══════════════════════════════════════════════════════════════════════
    Route::middleware('admin.permission:knowledge_hub.news')->group(function () {
        Route::get('admin/knowledge-hub/news', [NewsController::class, 'index'])->name('admin.knowledge-hub.news.index');
        Route::get('admin/knowledge-hub/news/create', [NewsController::class, 'create'])->name('admin.knowledge-hub.news.create');
        Route::post('admin/knowledge-hub/news', [NewsController::class, 'store'])->name('admin.knowledge-hub.news.store');
        Route::get('admin/knowledge-hub/news/{id}/edit', [NewsController::class, 'edit'])->name('admin.knowledge-hub.news.edit');
        Route::put('admin/knowledge-hub/news/{id}', [NewsController::class, 'update'])->name('admin.knowledge-hub.news.update');
        Route::delete('admin/knowledge-hub/news/{id}', [NewsController::class, 'destroy'])->name('admin.knowledge-hub.news.destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // KNOWLEDGE HUB - Events
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/knowledge-hub/events')->name('admin.knowledge-hub.events.')->middleware('admin.permission:knowledge_hub.events')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('index');
        Route::get('/create', [EventController::class, 'create'])->name('create');
        Route::post('/', [EventController::class, 'store'])->name('store');
        Route::get('/{event}/edit', [EventController::class, 'edit'])->name('edit');
        Route::put('/{event}', [EventController::class, 'update'])->name('update');
        Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // KNOWLEDGE HUB - Blogs
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/knowledge-hub/blogs')->name('admin.knowledge-hub.blogs.')->middleware('admin.permission:knowledge_hub.blogs')->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('index');
        Route::get('/create', [BlogController::class, 'create'])->name('create');
        Route::post('/', [BlogController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [BlogController::class, 'edit'])->name('edit');
        Route::put('/{id}', [BlogController::class, 'update'])->name('update');
        Route::delete('/{id}', [BlogController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/comments', [BlogController::class, 'addComment'])->name('comments.store');
        Route::put('/{blogId}/comments/{index}', [BlogController::class, 'updateComment'])->name('comments.update');
        Route::delete('/{blogId}/comments/{index}', [BlogController::class, 'deleteComment'])->name('comments.destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // KNOWLEDGE HUB - Price Promotions
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/knowledge-hub/price-promotions')->name('admin.knowledge-hub.price-promotions.')->middleware('admin.permission:knowledge_hub.price_promotions')->group(function () {
        Route::get('/', [PricePromotionController::class, 'index'])->name('index');
        Route::get('/create', [PricePromotionController::class, 'create'])->name('create');
        Route::post('/', [PricePromotionController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [PricePromotionController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PricePromotionController::class, 'update'])->name('update');
        Route::delete('/{id}', [PricePromotionController::class, 'destroy'])->name('destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // KNOWLEDGE HUB - PV Spot Price
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/knowledge-hub/pv-spot-price')->name('admin.knowledge-hub.pv-spot-price.')->middleware('admin.permission:knowledge_hub.pv_spot_price')->group(function () {
        Route::get('/', [PvSpotPriceController::class, 'index'])->name('index');
        Route::get('/create', [PvSpotPriceController::class, 'create'])->name('create');
        Route::post('/', [PvSpotPriceController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [PvSpotPriceController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PvSpotPriceController::class, 'update'])->name('update');
        Route::delete('/{id}', [PvSpotPriceController::class, 'destroy'])->name('destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // WAREHOUSES
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin')->name('admin.')->middleware('admin.permission:warehouses')->group(function () {
        Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('warehouses/{id}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::put('warehouses/{id}', [WarehouseController::class, 'update'])->name('warehouses.update');
        Route::patch('warehouses/{id}/mark-paid', [WarehouseController::class, 'markAsPaid'])->name('warehouses.mark-paid');
        Route::patch('warehouses/{id}/toggle', [WarehouseController::class, 'toggleStatus'])->name('warehouses.toggle-status');
        Route::delete('warehouses/{id}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // USER MANAGEMENT
    // ══════════════════════════════════════════════════════════════════════
    Route::middleware('admin.permission:users')->group(function () {
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{id}/update-basic', [UserController::class, 'updateBasic'])->name('admin.users.update-basic');
        Route::put('/admin/users/{id}/update-company', [UserController::class, 'updateCompany'])->name('admin.users.update-company');
        Route::patch('/admin/users/{id}/toggle-verified', [UserController::class, 'toggleCompanyVerified'])->name('admin.users.toggle-verified');
        Route::get('users/export', [UserController::class, 'export'])->name('admin.users.export');
        Route::post('/admin/users/{userId}/assign-admin', [UserController::class, 'assignAdmin'])->name('admin.users.assign-admin');
        Route::resource('users', UserController::class)->names('admin.users');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SCHEDULES
    // ══════════════════════════════════════════════════════════════════════
    Route::middleware('admin.permission:schedules')->group(function () {
        Route::get('/admin/schedules', [ScheduleController::class, 'index'])->name('admin.schedules.index');
    });

    // ══════════════════════════════════════════════════════════════════════
    // SALES
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/sales')->name('admin.sales.')->middleware('admin.permission:sales')->group(function () {
        Route::get('/', [SalesController::class, 'index'])->name('index');
        Route::post('/{id}/verify-payment', [SalesController::class, 'markPaymentVerified'])->name('verify-payment');
        Route::post('/{id}/status', [SalesController::class, 'updateStatus'])->name('update-status');
        Route::get('/{id}/proof', [SalesController::class, 'viewProof'])->name('proof');
    });

    // ══════════════════════════════════════════════════════════════════════
    // LEADS - Index
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/leads')->name('admin.leads.')->middleware('admin.permission:leads.index')->group(function () {
        Route::get('/', [LeadController::class, 'index'])->name('index');
        Route::get('/{id}/edit', [LeadController::class, 'edit'])->name('edit');
        Route::put('/{id}', [LeadController::class, 'update'])->name('update');
        Route::delete('/{id}', [LeadController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/status', [LeadController::class, 'updateStatus'])->name('update-status');
        Route::post('/{leadId}/assign-admin', [LeadController::class, 'assignAdmin'])->name('assign-admin');
    });

    // ══════════════════════════════════════════════════════════════════════
    // LEADS - Visits
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/leads/visits')->name('admin.leads.visits.')->middleware('admin.permission:leads.visits')->group(function () {
        Route::get('/', [LeadController::class, 'productVisits'])->name('index');
        Route::delete('/{id}', [LeadController::class, 'destroyVisit'])->name('destroy');
        Route::get('/export', [LeadController::class, 'exportVisits'])->name('export');
    });

    // ══════════════════════════════════════════════════════════════════════
    // BID/FAIR REQUESTS
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/bids')->name('admin.bids.')->middleware('admin.permission:bids')->group(function () {
        Route::get('/', [BidRequestController::class, 'index'])->name('index');
        Route::get('/{id}', [BidRequestController::class, 'show'])->name('show');
        Route::post('/{id}/status', [BidRequestController::class, 'updateStatus'])->name('update-status');
        Route::post('/{bidId}/assign-admin', [BidRequestController::class, 'assignAdmin'])->name('assign-admin');
        Route::delete('/{id}', [BidRequestController::class, 'destroy'])->name('destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // RFQ REQUESTS
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/rfq-requests')->name('admin.rfq-requests.')->middleware('admin.permission:rfq_requests')->group(function () {
        Route::get('/', [RfqRequestController::class, 'index'])->name('index');
        Route::get('/{id}', [RfqRequestController::class, 'show'])->name('show');
        Route::post('/{id}/status', [RfqRequestController::class, 'updateStatus'])->name('update-status');
        Route::post('/{rfqId}/assign-admin', [RfqRequestController::class, 'assignAdmin'])->name('assign-admin');
        Route::delete('/{id}', [RfqRequestController::class, 'destroy'])->name('destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // MANAGE LISTINGS
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('user/listings')->name('product_listing.')->middleware('admin.permission:listings')->group(function () {
        Route::get('/', [ProductListingController::class, 'index'])->name('index');
        Route::get('/create', [ProductListingController::class, 'create'])->name('create');
        Route::post('/', [ProductListingController::class, 'store'])->name('store');
        Route::get('/{id}', [ProductListingController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ProductListingController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ProductListingController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductListingController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle', [ProductListingController::class, 'toggleActive'])->name('toggle');
    });

    Route::middleware('admin.permission:listings')->group(function () {
        Route::post('product-listings/{id}/approve-payment', [ProductListingController::class, 'approvePayment'])->name('product_listing.approvePayment');
        Route::post('product-listings/{id}/approve-listing', [ProductListingController::class, 'approveListing'])->name('product_listing.approveListing');
    });

    // ══════════════════════════════════════════════════════════════════════
    // INVENTORY
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin')->name('admin.')->middleware('admin.permission:inventory')->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/{listingId}/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjust');
        Route::get('/inventory/{listingId}/history', [InventoryController::class, 'history'])->name('inventory.history');
        Route::get('/inventory/{listingId}/alert', [InventoryController::class, 'getAlert'])->name('inventory.alert.get');
        Route::post('/inventory/{listingId}/alert', [InventoryController::class, 'saveAlert'])->name('inventory.alert.save');
        Route::delete('/inventory/{listingId}/alert', [InventoryController::class, 'removeAlert'])->name('inventory.alert.remove');
        Route::post('/inventory/global-alert', [InventoryController::class, 'saveGlobalAlert'])->name('inventory.globalAlert');
    });

    // ══════════════════════════════════════════════════════════════════════
    // PRODUCTS - Index
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/products')->name('admin.products.')->middleware('admin.permission:products.index')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ProductController::class, 'update'])->name('update');
        Route::patch('/{id}/verify', [ProductController::class, 'verify'])->name('verify');
        Route::patch('/{id}/reject', [ProductController::class, 'reject'])->name('reject');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');
        Route::get('/options-by-submenu', [ProductController::class, 'getOptionsBySubMenu'])->name('options-by-submenu');
        Route::get('/sub-menus-by-main', [ProductController::class, 'getSubMenusByMainMenu'])->name('sub-menus-by-main');
    });

    // ══════════════════════════════════════════════════════════════════════
    // PRODUCTS - Specifications
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/products/detail-options')->name('admin.products.detail-options.')->middleware('admin.permission:products.specifications')->group(function () {
        Route::get('/', [ProductDetailOptionController::class, 'index'])->name('index');
        Route::get('/create', [ProductDetailOptionController::class, 'create'])->name('create');
        Route::post('/', [ProductDetailOptionController::class, 'store'])->name('store');
        Route::get('/{detailOption}/edit', [ProductDetailOptionController::class, 'edit'])->name('edit');
        Route::put('/{detailOption}', [ProductDetailOptionController::class, 'update'])->name('update');
        Route::delete('/{detailOption}', [ProductDetailOptionController::class, 'destroy'])->name('destroy');
    });

    // ══════════════════════════════════════════════════════════════════════
    // TRANSLATIONS (utility routes - no specific permission needed)
    // ══════════════════════════════════════════════════════════════════════
    Route::prefix('admin/translations')->name('admin.translations.')->group(function () {
        Route::post('/switch-language', [TranslationController::class, 'switchLanguage'])->name('switch-language');
        Route::post('/translate-all', [TranslationController::class, 'translateAll'])->name('translate-all');
        Route::post('/translate-record', [TranslationController::class, 'translateRecord'])->name('translate-record');
        Route::post('/translate-all-models', [TranslationController::class, 'translateAllModels'])->name('translate-all-models');
    });

    // ══════════════════════════════════════════════════════════════════════
    // API ROUTES (utility routes - no specific permission needed)
    // ══════════════════════════════════════════════════════════════════════
    Route::get('/api/subcategories/{mainCategoryId}', [ProductListingController::class, 'getSubCategories']);
    Route::get('/api/products/{subCategoryId}', [ProductListingController::class, 'getProducts']);
    Route::get('/api/warehouses', [ProductListingController::class, 'getWarehouses']);

});

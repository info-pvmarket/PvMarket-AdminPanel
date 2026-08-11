<?php

namespace Tests\Unit;

use App\Services\ProductCsvExporter;
use App\Services\ProductListingCsvExporter;
use PHPUnit\Framework\TestCase;

class AdminListPresentationTest extends TestCase
{
    private function projectFile(string $path): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function test_user_management_has_real_status_and_delete_forms(): void
    {
        $routes = file_get_contents($this->projectFile('routes/web.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/users/index.blade.php'));

        $this->assertStringContainsString("name('admin.users.toggle-status')", $routes);
        $this->assertStringContainsString("name('admin.users.destroy')", $routes);
        $this->assertStringNotContainsString("Route::resource('users'", $routes);
        $this->assertStringContainsString("route('admin.users.toggle-status'", $view);
        $this->assertStringContainsString("route('admin.users.destroy'", $view);
    }

    public function test_disabling_a_user_places_all_of_their_listings_on_hold(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/UserController.php'));

        $this->assertStringContainsString('$isActive = !$user->isActiveForManagement();', $controller);
        $this->assertStringContainsString('$user->is_hold = !$isActive;', $controller);
        $this->assertStringContainsString(
            "ProductListing::where('user_id', new ObjectId((string) \$user->_id))",
            $controller
        );
        $this->assertStringContainsString("'is_hold' => true", $controller);
        $this->assertStringContainsString("'is_active' => false", $controller);
    }

    public function test_user_management_list_displays_phone_numbers(): void
    {
        $view = file_get_contents($this->projectFile('resources/views/admin/users/index.blade.php'));

        $this->assertStringContainsString('<th>Phone Number</th>', $view);
        $this->assertStringContainsString("\$user->mobile ?? \$user->phone ?? ''", $view);
        $this->assertStringContainsString('href="tel:', $view);
        $this->assertStringContainsString('<td colspan="7">', $view);
    }

    public function test_inventory_uses_cards_for_filters_and_exposes_requested_columns(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/InventoryController.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/inventory/index.blade.php'));

        $this->assertStringContainsString('class="stat-card card-blue', $view);
        $this->assertStringContainsString("'filter' => 'recent_movements'", $view);
        $this->assertStringContainsString("\$filter === 'recent_movements'", $view);
        $this->assertStringContainsString('name="sort"', $view);
        $this->assertStringContainsString('<th>Product Name</th>', $view);
        $this->assertStringContainsString("route('product_listing.index'", $view);
        $this->assertStringNotContainsString('class="filter-pills"', $view);
        $this->assertStringContainsString("'recent_movements']", $controller);
        $this->assertStringContainsString("->pluck('listing_id')", $controller);
        $this->assertStringContainsString('->unique()', $controller);
        $this->assertStringContainsString("\$filter === 'recent_movements'", $controller);
        $this->assertStringContainsString(
            '$recentMovements = $recentMovementListingIds->count();',
            $controller
        );
    }

    public function test_product_and_listing_lists_show_created_and_updated_dates(): void
    {
        $products = file_get_contents($this->projectFile('resources/views/admin/products/products.blade.php'));
        $listings = file_get_contents($this->projectFile('resources/views/admin/product_listing/index.blade.php'));
        $listingController = file_get_contents($this->projectFile('app/Http/Controllers/Admin/ProductListingController.php'));

        $this->assertStringContainsString('Sort products by created date', $products);
        $this->assertStringContainsString('<th style="width:150px;">Created Date</th>', $products);
        $this->assertStringContainsString('<label>Created Date</label>', $listings);
        $this->assertStringContainsString('<label>Last Updated At</label>', $listings);
        $this->assertSame(2, substr_count($listingController, "orderBy('updated_at', 'desc')"));
    }

    public function test_product_list_filters_by_category_and_subcategory(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/ProductController.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/products/products.blade.php'));

        $this->assertStringContainsString("\$query->where('category_id', \$categoryFilterId)", $controller);
        $this->assertStringContainsString("\$query->where('sub_category_id', \$subCategoryFilterId)", $controller);
        $this->assertStringContainsString("\$filterSubMenusQuery->where('category_id', \$categoryFilterId)", $controller);
        $this->assertStringContainsString('name="category_id"', $view);
        $this->assertStringContainsString('name="sub_category_id"', $view);
        $this->assertStringContainsString('All Categories', $view);
        $this->assertStringContainsString('All Subcategories', $view);
    }

    public function test_product_csv_export_honors_list_filters_and_exposes_catalog_fields(): void
    {
        $routes = file_get_contents($this->projectFile('routes/web.php'));
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/ProductController.php'));
        $exporter = file_get_contents($this->projectFile('app/Services/ProductCsvExporter.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/products/products.blade.php'));

        $this->assertStringContainsString("[ProductController::class, 'export'])->name('export')", $routes);
        $this->assertStringContainsString('public function export(Request $request, ProductCsvExporter $exporter)', $controller);
        $this->assertStringContainsString('$exporter->download($products)', $controller);
        $this->assertStringContainsString("route('admin.products.export'", $view);
        $this->assertStringContainsString('Export CSV', $view);
        $this->assertStringContainsString("'verification_status', 'listings_filter', 'search', 'sort', 'category_id', 'sub_category_id'", $view);
        $this->assertContains('SKU Code', ProductCsvExporter::HEADERS);
        $this->assertContains('Product Badge', ProductCsvExporter::HEADERS);
        $this->assertContains('Brand', ProductCsvExporter::HEADERS);
        $this->assertContains('Category', ProductCsvExporter::HEADERS);
        $this->assertContains('Subcategory', ProductCsvExporter::HEADERS);
        $this->assertContains('Created At', ProductCsvExporter::HEADERS);
        $this->assertStringContainsString('fwrite($handle, "\\xEF\\xBB\\xBF")', $exporter);
    }

    public function test_admin_product_create_and_edit_enforce_unique_names(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/ProductController.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/products/products.blade.php'));

        $this->assertSame(2, substr_count($controller, "'product_name' => trim((string) \$request->input('product_name'))"));
        $this->assertStringContainsString("'product_name'   => \$this->productNameRules(),", $controller);
        $this->assertStringContainsString("'product_name'    => \$this->productNameRules((string) \$product->_id),", $controller);
        $this->assertStringContainsString("new \\MongoDB\\BSON\\Regex(\$pattern, 'i')", $controller);
        $this->assertStringContainsString("'A product with this name already exists.'", $controller);
        $this->assertStringContainsString("@error('product_name')", $view);
        $this->assertStringContainsString('class="field-error"', $view);
    }

    public function test_verifying_a_product_also_activates_it(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/ProductController.php'));
        $model = file_get_contents($this->projectFile('app/Models/Product.php'));

        $this->assertMatchesRegularExpression(
            '/function verify\(\$id\).*?\'verification_status\'\s*=>\s*\'verified\'.*?\'is_active\'\s*=>\s*true/s',
            $controller
        );
        $this->assertStringContainsString("'is_active',", $model);
        $this->assertStringContainsString("'is_active'       => 'boolean'", $model);
    }

    public function test_listing_csv_export_includes_brand_discount_sold_off_and_dates(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/ProductListingController.php'));
        $exporter = file_get_contents($this->projectFile('app/Services/ProductListingCsvExporter.php'));

        $this->assertContains('Brand', ProductListingCsvExporter::HEADERS);
        $this->assertContains('Discount Type', ProductListingCsvExporter::HEADERS);
        $this->assertContains('Sold Off', ProductListingCsvExporter::HEADERS);
        $this->assertContains('Created At', ProductListingCsvExporter::HEADERS);
        $this->assertContains('Updated At', ProductListingCsvExporter::HEADERS);
        $this->assertStringContainsString('$product->brand_name ?? $listing->brand_name ?? \'\',', $exporter);
        $this->assertStringContainsString('$listing->discount_type ?? \'\',', $exporter);
        $this->assertStringContainsString("(\$listing->is_sold_off ?? false) ? 'Yes' : 'No',", $exporter);
        $this->assertStringContainsString('$this->formatDate($listing->created_at ?? null),', $exporter);
        $this->assertStringContainsString('$this->formatDate($listing->updated_at ?? null),', $exporter);
        $this->assertStringContainsString('ProductListingCsvExporter $exporter', $controller);
        $this->assertStringContainsString('$exporter->download(', $controller);
    }

    public function test_user_edit_listings_show_dates_and_export_only_that_users_filtered_records(): void
    {
        $routes = file_get_contents($this->projectFile('routes/web.php'));
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/UserController.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/users/edit.blade.php'));

        $this->assertStringContainsString("name('admin.users.listings.export')", $routes);
        $this->assertStringContainsString('public function exportUserListings(', $controller);
        $this->assertStringContainsString("ProductListing::where('user_id', \$userId)", $controller);
        $this->assertStringContainsString('$this->applyUserListingFilters($query, $request);', $controller);
        $this->assertStringContainsString('$exporter->download(', $controller);
        $this->assertStringContainsString('<label>Listed On</label>', $view);
        $this->assertStringContainsString('<label>Last Updated At</label>', $view);
        $this->assertStringContainsString("route('admin.users.listings.export'", $view);
        $this->assertStringContainsString("'listing_filter'", $view);
        $this->assertStringContainsString("'listing_status'", $view);
        $this->assertStringContainsString("'listing_payment'", $view);
        $this->assertStringContainsString("'listing_realtime'", $view);
    }

    public function test_listing_search_resolves_product_names_through_real_mongodb_ids(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/ProductListingController.php'));

        $this->assertSame(2, substr_count($controller, '$this->searchProductIdCandidates($search)'));
        $this->assertStringContainsString('private function searchProductIdCandidates(string $search): array', $controller);
        $this->assertStringContainsString("->get(['_id'])", $controller);
        $this->assertStringContainsString('->flatMap(fn($product) => $this->mongoIdCandidates($product->_id))', $controller);
        $this->assertStringNotContainsString("->pluck('_id')\n                    ->flatMap(fn(\$id) => \$this->mongoIdCandidates(\$id))", $controller);
    }

    public function test_sold_off_toggle_does_not_change_hold_state(): void
    {
        $createView = file_get_contents($this->projectFile('resources/views/admin/product_listing/create.blade.php'));
        $editView = file_get_contents($this->projectFile('resources/views/admin/product_listing/edit.blade.php'));

        foreach ([$createView, $editView] as $view) {
            $this->assertStringContainsString('function syncOfferStatusSummary()', $view);
            $this->assertStringContainsString("const isSoldOff", $view);

            $soldHandlerStart = strpos($view, "document.getElementById('toggleSoldOff').addEventListener");
            $popularHandlerStart = strpos($view, "document.getElementById('togglePopular').addEventListener");
            $this->assertNotFalse($soldHandlerStart);
            $this->assertNotFalse($popularHandlerStart);

            $soldHandler = substr($view, $soldHandlerStart, $popularHandlerStart - $soldHandlerStart);
            $this->assertStringNotContainsString("toggleIsActive').checked", $soldHandler);
        }
    }

    public function test_listing_edit_uses_a_mark_as_hold_toggle(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/ProductListingController.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/product_listing/edit.blade.php'));

        $this->assertStringContainsString('<div class="toggle-info-title">Mark as Hold</div>', $view);
        $this->assertStringNotContainsString('<div class="toggle-info-title">Status</div>', $view);
        $this->assertStringContainsString('name="is_on_hold" value="1"', $view);
        $this->assertStringContainsString("\$isOfferOnHold ? 'checked' : ''", $view);
        $this->assertStringContainsString("'Inactive' : 'Active'", $view);
        $this->assertSame(2, substr_count($controller, "'is_on_hold'                       => 'nullable|boolean'"));
        $this->assertSame(2, substr_count($controller, "! \$request->boolean('is_on_hold', false)"));
    }

    public function test_manage_listings_has_an_active_inactive_toggle(): void
    {
        $routes = file_get_contents($this->projectFile('routes/web.php'));
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/ProductListingController.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/product_listing/index.blade.php'));

        $this->assertStringContainsString("name('toggle')", $routes);
        $this->assertStringContainsString("route('product_listing.toggle'", $view);
        $this->assertStringContainsString("@method('PATCH')", $view);
        $this->assertStringContainsString("'is-active' : 'is-inactive'", $view);
        $this->assertStringContainsString('<span class="listing-status-label">Status</span>', $view);
        $this->assertStringContainsString('class="listing-status-track"', $view);
        $this->assertStringContainsString("'Active' : 'Inactive'", $view);
        $this->assertStringContainsString('Listing is now inactive.', $controller);
    }

    public function test_super_admin_category_label_matches_main_menu_context(): void
    {
        $sidebar = file_get_contents($this->projectFile('resources/views/components/admin/sidebar.blade.php'));
        $mainMenu = file_get_contents($this->projectFile('resources/views/admin/setup/main-menu/main-menu.blade.php'));

        $this->assertStringContainsString('Categories/main menu', $sidebar);
        $this->assertStringContainsString('Categories/main menu', $mainMenu);
    }

    public function test_sub_category_pages_hide_legacy_stock_and_shipping_fields(): void
    {
        $view = file_get_contents($this->projectFile('resources/views/admin/setup/sub-menu/sub-menu.blade.php'));
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/SubMenuController.php'));

        $this->assertStringNotContainsString('Pallet', $view);
        $this->assertStringNotContainsString('Container', $view);
        $this->assertStringNotContainsString('Value of Stocks', $view);
        $this->assertStringNotContainsString('name="stock_value"', $view);
        $this->assertStringNotContainsString('stock-toggle', $view);
        $this->assertStringContainsString('availableMainMenus', $controller);
        $this->assertStringNotContainsString("where('stock_value', true)", $controller);
        $this->assertStringNotContainsString("\$request->has('pallet_applicable')", $controller);
        $this->assertStringNotContainsString("\$request->has('container_applicable')", $controller);
    }

    public function test_main_menu_list_hides_value_of_stocks(): void
    {
        $view = file_get_contents($this->projectFile('resources/views/admin/setup/main-menu/main-menu.blade.php'));

        $this->assertStringNotContainsString('Value of Stocks', $view);
        $this->assertStringNotContainsString('name="stock_value"', $view);
        $this->assertStringNotContainsString('stock-toggle', $view);
    }

    public function test_category_and_sub_category_actions_toggle_active_status(): void
    {
        $mainController = file_get_contents($this->projectFile('app/Http/Controllers/Admin/MainMenuController.php'));
        $subController = file_get_contents($this->projectFile('app/Http/Controllers/Admin/SubMenuController.php'));
        $mainView = file_get_contents($this->projectFile('resources/views/admin/setup/main-menu/main-menu.blade.php'));
        $subView = file_get_contents($this->projectFile('resources/views/admin/setup/sub-menu/sub-menu.blade.php'));

        $this->assertStringContainsString("'is_active' => \$isActive", $mainController);
        $this->assertStringContainsString("'is_active' => \$isActive", $subController);
        $this->assertStringContainsString('MainMenu::availableForDropdown()', $subController);
        $this->assertStringNotContainsString("request->boolean('is_active', true)", $mainController);
        $this->assertStringContainsString('$menu->is_active', $mainView);
        $this->assertStringContainsString('$sub->is_active', $subView);
        $this->assertStringNotContainsString('$sub->is_hold', $subView);
    }

    public function test_brand_list_supports_newest_and_oldest_created_date_sorting(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/BrandController.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/setup/brands/brands.blade.php'));

        $this->assertStringContainsString("['newest', 'oldest']", $controller);
        $this->assertStringContainsString("orderBy('created_at', \$sort === 'oldest' ? 'asc' : 'desc')", $controller);
        $this->assertStringContainsString('name="sort"', $view);
        $this->assertStringContainsString('>Newest</option>', $view);
        $this->assertStringContainsString('>Oldest</option>', $view);
        $this->assertStringContainsString("request('sort', 'newest')", $view);
    }

    public function test_brand_active_action_replaces_show_in_menu_control(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/BrandController.php'));
        $model = file_get_contents($this->projectFile('app/Models/Brand.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/setup/brands/brands.blade.php'));

        $this->assertStringContainsString("'is_active' => \$isActive", $controller);
        $this->assertStringContainsString("'is_hold'   => !\$isActive", $controller);
        $this->assertStringContainsString('$brand->is_active', $view);
        $this->assertStringContainsString('Zero appears last', $view);
        $this->assertStringNotContainsString('can_show_menu', $controller);
        $this->assertStringNotContainsString('can_show_menu', $model);
        $this->assertStringNotContainsString('can_show_menu', $view);
        $this->assertStringNotContainsString('Show in Menu', $view);
    }

    public function test_market_list_does_not_show_the_power_toggle_action(): void
    {
        $view = file_get_contents($this->projectFile('resources/views/admin/setup/markets/index.blade.php'));

        $this->assertStringContainsString('badge-active', $view);
        $this->assertStringContainsString('badge-inactive', $view);
        $this->assertStringNotContainsString('Toggle Status', $view);
        $this->assertStringNotContainsString("route('admin.setup.markets.toggle'", $view);
        $this->assertStringNotContainsString('action-icon toggle', $view);
    }

    public function test_market_create_form_is_simplified_and_supports_calendly(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/MarketController.php'));
        $createView = file_get_contents($this->projectFile('resources/views/admin/setup/markets/create.blade.php'));
        $editView = file_get_contents($this->projectFile('resources/views/admin/setup/markets/edit.blade.php'));

        $this->assertStringContainsString('name="calendly_link"', $createView);
        $this->assertStringContainsString('name="calendly_link"', $editView);
        $this->assertStringContainsString("'calendly_link' => 'nullable|url|max:2048'", $controller);
        $this->assertStringContainsString("'default_locale'   => 'en-US'", $controller);
        $this->assertStringNotContainsString('name="default_currency"', $createView);
        $this->assertStringNotContainsString('name="default_locale"', $createView);
        $this->assertStringNotContainsString('Site Settings (Optional)', $createView);
        $this->assertStringNotContainsString('Analytics (Optional)', $createView);
        $this->assertStringNotContainsString('name="gtm_container_id"', $createView);
        $this->assertStringNotContainsString('name="google_analytics_id"', $createView);
    }

    public function test_market_edit_form_only_keeps_identity_contact_and_product_filtering(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/MarketController.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/setup/markets/edit.blade.php'));

        $this->assertStringContainsString('name="code"', $view);
        $this->assertStringContainsString('name="name"', $view);
        $this->assertStringContainsString('Product Filtering', $view);
        $this->assertStringContainsString('Contact Information', $view);
        $this->assertStringContainsString('name="calendly_link"', $view);
        $this->assertStringNotContainsString('name="default_currency"', $view);
        $this->assertStringNotContainsString('name="default_locale"', $view);
        $this->assertStringNotContainsString('name="is_active"', $view);
        $this->assertStringNotContainsString('Site Settings', $view);
        $this->assertStringNotContainsString('Social Links', $view);
        $this->assertStringNotContainsString('<h3 class="section-title">Analytics</h3>', $view);
        $this->assertStringNotContainsString('<h3 class="section-title">Features</h3>', $view);
        $this->assertStringNotContainsString('Domain Mappings', $view);
        $this->assertStringNotContainsString("'default_currency' => strtoupper(\$request->default_currency)", $controller);
        $this->assertStringNotContainsString("'is_active'        => \$request->boolean('is_active')", $controller);
    }

    public function test_market_code_is_selected_from_countries_and_normalized_for_storage(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/MarketController.php'));
        $createView = file_get_contents($this->projectFile('resources/views/admin/setup/markets/create.blade.php'));
        $editView = file_get_contents($this->projectFile('resources/views/admin/setup/markets/edit.blade.php'));

        $this->assertStringContainsString('<select name="code"', $createView);
        $this->assertStringContainsString('<select name="code"', $editView);
        $this->assertStringContainsString('$marketCountries as $country', $createView);
        $this->assertStringContainsString('$marketCountries as $country', $editView);
        $this->assertStringNotContainsString('<input type="text" name="code"', $createView);
        $this->assertStringNotContainsString('<input type="text" name="code"', $editView);
        $this->assertStringContainsString("'code' => strtolower(trim((string) \$request->code))", $controller);
        $this->assertStringContainsString("preg_match('/^[a-z]{2}$/', (string) \$value)", $controller);
        $this->assertStringContainsString("->where('iso2', \$countryCode)", $controller);
        $this->assertStringContainsString("'code' => \$request->code", $controller);
        $this->assertStringContainsString("'default_country_code' => strtoupper(\$request->code)", $controller);
        $this->assertStringContainsString("'filter_by_country'    => true", $controller);
    }

    public function test_currency_management_uses_a_global_collection_and_is_available_in_setup(): void
    {
        $routes = file_get_contents($this->projectFile('routes/web.php'));
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/CurrencyController.php'));
        $currencyModel = file_get_contents($this->projectFile('app/Models/Currency.php'));
        $marketController = file_get_contents($this->projectFile('app/Http/Controllers/Admin/MarketController.php'));
        $listingController = file_get_contents($this->projectFile('app/Http/Controllers/Admin/ProductListingController.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/setup/currencies/index.blade.php'));
        $sidebar = file_get_contents($this->projectFile('resources/views/components/admin/sidebar.blade.php'));

        $this->assertStringContainsString("name('admin.setup.currencies.')", $routes);
        $this->assertStringContainsString("route('admin.setup.currencies.index')", $sidebar);
        $this->assertStringContainsString("protected \$collection = 'currencies'", $currencyModel);
        $this->assertStringContainsString('Currency::orderBy', $controller);
        $this->assertStringContainsString('Global currencies', $view);
        $this->assertStringContainsString('Add Currency', $view);
        $this->assertStringContainsString('Currency Symbol', $view);
        $this->assertStringContainsString("route('admin.setup.currencies.symbol'", $view);
        $this->assertStringNotContainsString('market_id', $controller);
        $this->assertStringNotContainsString('Set Default', $view);
        $this->assertStringContainsString("Currency::where('code', 'USD')", $marketController);
        $this->assertStringNotContainsString('name="default_currency"', file_get_contents($this->projectFile('resources/views/admin/setup/markets/edit.blade.php')));
        $this->assertStringContainsString('$this->availableCurrencies()', $listingController);
        $this->assertStringContainsString("Currency::orderBy('code')", $listingController);
        $this->assertStringNotContainsString("\$currencies    = ['AED', 'USD', 'GBP', 'EUR'];", $listingController);
    }

    public function test_manage_listings_resolves_warehouse_country_ids_to_readable_names(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/ProductListingController.php'));
        $warehouseModel = file_get_contents($this->projectFile('app/Models/Warehouse.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/product_listing/index.blade.php'));

        $this->assertStringContainsString("Country::whereIn('_id', \$countryIds)", $controller);
        $this->assertStringContainsString("'countriesMap'", $controller);
        $this->assertStringContainsString("\$countryName   = \$country", $view);
        $this->assertStringContainsString("trim(lang(\$country, 'name'))", $view);
        $this->assertStringContainsString("trim(lang(\$warehouse, 'country_name'))", $view);
        $this->assertStringContainsString('{{ $locationName }}', $view);
        $this->assertStringNotContainsString("lang(\$warehouse, 'country')", $view);
        $this->assertStringContainsString("'country_name'", $warehouseModel);
        $this->assertStringNotContainsString("\n        'country',\n", $warehouseModel);
    }

    public function test_manage_listings_resolves_incoterm_ids_to_code_and_name(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/ProductListingController.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/product_listing/index.blade.php'));

        $this->assertStringContainsString("\$listings->pluck('incoterms_id')", $controller);
        $this->assertStringContainsString("Incoterm::whereIn('_id', \$incotermIds)", $controller);
        $this->assertStringContainsString("'incotermsMap'", $controller);
        $this->assertStringContainsString("\$incotermLabel = \$incoterm", $view);
        $this->assertStringContainsString("trim((string) \$incoterm->code)", $view);
        $this->assertStringContainsString("trim(lang(\$incoterm, 'name'))", $view);
        $this->assertStringContainsString('Incoterm: {{ $incotermLabel }}', $view);
        $this->assertStringNotContainsString("Incoterm: {{ \$listing->incoterms_id", $view);
    }

    public function test_manage_listings_filters_by_category_subcategory_and_brand(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/ProductListingController.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/product_listing/index.blade.php'));

        $this->assertStringContainsString("request->get('category_id'", $controller);
        $this->assertStringContainsString("request->get('sub_category_id'", $controller);
        $this->assertStringContainsString("request->get('brand_id'", $controller);
        $this->assertSame(2, substr_count($controller, '$this->applyProductFilters('));
        $this->assertStringContainsString('MainMenu::availableForDropdown()', $controller);
        $this->assertStringContainsString('SubMenu::availableForDropdown()', $controller);
        $this->assertStringContainsString("Brand::where('is_active', true)", $controller);
        $this->assertStringContainsString("'category_id',", $controller);
        $this->assertStringContainsString("'sub_category_id',", $controller);
        $this->assertStringContainsString("'brand_id',", $controller);
        $this->assertStringContainsString('name="category_id"', $view);
        $this->assertStringContainsString('name="sub_category_id"', $view);
        $this->assertStringContainsString('name="brand_id"', $view);
        $this->assertStringContainsString("'category_id', 'sub_category_id', 'brand_id'", $view);
    }

    public function test_product_forms_have_a_database_backed_product_badge_before_details(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/ProductController.php'));
        $model = file_get_contents($this->projectFile('app/Models/Product.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/products/products.blade.php'));

        $this->assertLessThan(strpos($view, '<div class="section-title">Product Details</div>'), strpos($view, '<div class="section-title">Product Badge</div>'));
        $this->assertStringContainsString('name="specific_value"', $view);
        $this->assertStringContainsString('name="specific_value_unit_id"', $view);
        $this->assertStringContainsString('Specification <span>*</span>', $view);
        $this->assertStringContainsString('<label class="form-label">Unit</label>', $view);
        $this->assertStringContainsString("Unit::where('is_active', true)->orderBy('unit_name')->get()", $controller);
        $this->assertStringContainsString("'specific_value' => 'required|string|max:255'", $controller);
        $this->assertStringContainsString("'specific_value_unit_id' => ['nullable'", $controller);
        $this->assertStringContainsString("'specific_value'", $model);
        $this->assertStringContainsString("'specific_value_unit_id'", $model);
    }
}

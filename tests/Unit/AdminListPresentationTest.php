<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AdminListPresentationTest extends TestCase
{
    private function projectFile(string $path): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
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

    public function test_inventory_uses_cards_for_filters_and_exposes_requested_columns(): void
    {
        $view = file_get_contents($this->projectFile('resources/views/admin/inventory/index.blade.php'));

        $this->assertStringContainsString('class="stat-card card-blue', $view);
        $this->assertStringContainsString('name="sort"', $view);
        $this->assertStringContainsString('<th>Product Name</th>', $view);
        $this->assertStringContainsString("route('product_listing.index'", $view);
        $this->assertStringNotContainsString('class="filter-pills"', $view);
    }

    public function test_product_and_listing_lists_show_created_dates(): void
    {
        $products = file_get_contents($this->projectFile('resources/views/admin/products/products.blade.php'));
        $listings = file_get_contents($this->projectFile('resources/views/admin/product_listing/index.blade.php'));

        $this->assertStringContainsString('Sort products by created date', $products);
        $this->assertStringContainsString('<th style="width:150px;">Created Date</th>', $products);
        $this->assertStringContainsString('<label>Created Date</label>', $listings);
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
        $this->assertStringContainsString("where('is_active', true)", $subController);
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
}

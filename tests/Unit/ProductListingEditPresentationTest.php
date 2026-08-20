<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ProductListingEditPresentationTest extends TestCase
{
    private function projectFile(string $path): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function test_edit_sell_types_match_the_seller_dashboard(): void
    {
        $controller = file_get_contents($this->projectFile(
            'app/Http/Controllers/Admin/ProductListingController.php'
        ));
        $view = file_get_contents($this->projectFile(
            'resources/views/admin/product_listing/edit.blade.php'
        ));

        $this->assertStringContainsString("'sell by pieces' => 'Sell By Pieces Only'", $controller);
        $this->assertStringContainsString("'sell by pallets' => 'Sell By Pallets Only'", $controller);
        $this->assertStringContainsString("'sell by containers' => 'Sell By Containers Only'", $controller);
        $this->assertStringContainsString('@foreach($sellTypes as $val => $label)', $view);
        $this->assertStringNotContainsString('Sell By Weight', $view);
    }

    public function test_quantity_units_follow_the_selected_sell_type(): void
    {
        $view = file_get_contents($this->projectFile(
            'resources/views/admin/product_listing/edit.blade.php'
        ));

        $this->assertStringContainsString('id="totalQtyUnit"', $view);
        $this->assertStringContainsString('function getQuantityUnit(sellType)', $view);
        $this->assertStringContainsString("'sell by pieces': 'pcs'", $view);
        $this->assertStringContainsString("'sell by pallets': 'pallets'", $view);
        $this->assertStringContainsString("'sell by containers': 'container'", $view);
        $this->assertStringContainsString('syncQuantityPresentation();', $view);
    }

    public function test_image_count_uses_the_explicit_listing_image_query(): void
    {
        $view = file_get_contents($this->projectFile(
            'resources/views/admin/product_listing/edit.blade.php'
        ));

        $this->assertStringContainsString('$listingImageCount = $listingImages->count();', $view);
        $this->assertStringContainsString('<img src="{{ $img->public_url }}"', $view);
        $this->assertStringContainsString('let existingImageCount = {{ $listingImageCount }};', $view);
        $this->assertStringNotContainsString('$listing->images->count()', $view);
    }

    public function test_listing_index_uses_sell_type_for_total_available_unit(): void
    {
        $view = file_get_contents($this->projectFile(
            'resources/views/admin/product_listing/index.blade.php'
        ));

        $this->assertStringContainsString('ProductListing::quantityUnitForSellType(', $view);
        $this->assertStringContainsString("\$listing->getRawOriginal('sell_type')", $view);
        $this->assertStringContainsString(
            '{{ number_format($listing->total_quantity) }} {{ $quantityUnit }}',
            $view
        );
        $this->assertStringNotContainsString(
            '{{ number_format($listing->total_quantity) }} pcs',
            $view
        );
    }

    public function test_manage_listings_and_export_exclude_soft_deleted_records(): void
    {
        $model = file_get_contents($this->projectFile(
            'app/Models/ProductListing.php'
        ));
        $controller = file_get_contents($this->projectFile(
            'app/Http/Controllers/Admin/ProductListingController.php'
        ));

        $this->assertStringContainsString('function scopeNotDeleted($query)', $model);
        $this->assertStringContainsString("return \$query->whereNull('deleted_at');", $model);
        $this->assertSame(3, substr_count($controller, 'ProductListing::notDeleted()'));
    }

    public function test_admin_listing_edit_records_and_displays_price_history(): void
    {
        $routes = file_get_contents($this->projectFile('routes/web.php'));
        $controller = file_get_contents($this->projectFile(
            'app/Http/Controllers/Admin/ProductListingController.php'
        ));
        $view = file_get_contents($this->projectFile(
            'resources/views/admin/product_listing/edit.blade.php'
        ));

        $this->assertStringContainsString("Route::prefix('user/listings')", $routes);
        $this->assertStringContainsString(
            "Route::put('/{id}', [ProductListingController::class, 'update'])",
            $routes
        );
        $this->assertStringContainsString('PriceTransaction::forListing($id)', $controller);
        $this->assertStringContainsString('$this->priceHistoryService->record(', $controller);
        $this->assertStringContainsString('Price History', $view);
        $this->assertStringContainsString('$tx->transaction_label', $view);
    }

    public function test_total_quantity_changes_replace_the_adjust_stock_modal(): void
    {
        $controller = file_get_contents($this->projectFile(
            'app/Http/Controllers/Admin/ProductListingController.php'
        ));
        $view = file_get_contents($this->projectFile(
            'resources/views/admin/product_listing/edit.blade.php'
        ));

        $this->assertStringContainsString('name="inventory_notes"', $view);
        $this->assertStringContainsString('syncInventoryNotesVisibility()', $view);
        $this->assertStringNotContainsString('onclick="openAdjust(this)"', $view);
        $this->assertStringNotContainsString('id="adjustModal"', $view);
        $this->assertStringContainsString(
            "'inventory_notes.required' => 'Please enter notes explaining the total quantity change.'",
            $controller
        );
        $this->assertStringContainsString(
            '$this->inventoryHistoryService->recordTotalQuantityChange(',
            $controller
        );
    }
}

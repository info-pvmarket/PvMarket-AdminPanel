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

        $this->assertStringContainsString("str_contains(\$normalizedSellType, 'pallet') => 'pallets'", $view);
        $this->assertStringContainsString("str_contains(\$normalizedSellType, 'container') => 'container'", $view);
        $this->assertStringContainsString("default => 'pcs'", $view);
        $this->assertStringContainsString(
            '{{ number_format($listing->total_quantity) }} {{ $quantityUnit }}',
            $view
        );
        $this->assertStringNotContainsString(
            '{{ number_format($listing->total_quantity) }} pcs',
            $view
        );
    }
}

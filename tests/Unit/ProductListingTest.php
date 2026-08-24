<?php

namespace Tests\Unit;

use App\Models\ProductListing;
use PHPUnit\Framework\TestCase;

class ProductListingTest extends TestCase
{
    public function test_it_uses_the_shared_product_listings_schema(): void
    {
        $listing = new ProductListing();

        $this->assertSame('product_listings', $listing->getTable());
        $this->assertContains('incoterms_id', $listing->getFillable());
        $this->assertArrayHasKey('incoterms_id', $listing->getCasts());
        $this->assertContains('is_hold', $listing->getFillable());
        $this->assertArrayHasKey('is_hold', $listing->getCasts());
        $this->assertNotContains('incoterm_id', $listing->getFillable());
    }

    public function test_hidden_tier_editor_controls_do_not_participate_in_form_validation(): void
    {
        foreach (['create', 'edit'] as $view) {
            $contents = file_get_contents(
                dirname(__DIR__, 2)."/resources/views/admin/product_listing/{$view}.blade.php"
            );

            $this->assertIsString($contents);
            foreach (['slotMinQty', 'slotMaxQty', 'slotCommission', 'slotPrice', 'slotTotalPrice'] as $control) {
                $this->assertMatchesRegularExpression(
                    '/id="'.$control.'"[^>]*\sdisabled(?:\s|>)/',
                    $contents
                );
            }
            $this->assertStringContainsString(
                'maxQuantityInput.disabled = !usesSpecificMaximum;',
                $contents
            );
            $this->assertStringContainsString(
                'setSlotEditorInputsEnabled(false);',
                $contents
            );
        }
    }

    public function test_quantity_unit_handles_current_and_legacy_sell_type_values(): void
    {
        $this->assertSame('pcs', ProductListing::quantityUnitForSellType('sell by pieces'));
        $this->assertSame('pallets', ProductListing::quantityUnitForSellType('Sell By Pallets Only'));
        $this->assertSame('container', ProductListing::quantityUnitForSellType('sell by containers'));
        $this->assertSame('pcs', ProductListing::quantityUnitForSellType(null));
        $this->assertSame('pcs', ProductListing::quantityUnitForSellType([]));
        $this->assertSame('pcs', ProductListing::quantityUnitForSellType(new \stdClass()));
    }

    public function test_sell_type_normalization_uses_the_api_canonical_values(): void
    {
        $this->assertSame('sell by pieces', ProductListing::normalizeSellType('Sell By Pieces Only'));
        $this->assertSame('sell by pallets', ProductListing::normalizeSellType('Sell By Pallets Only'));
        $this->assertSame('sell by container', ProductListing::normalizeSellType('sell by containers'));
        $this->assertSame('sell by container', ProductListing::normalizeSellType('sell by container'));
    }

    public function test_container_quantities_convert_between_display_units_and_canonical_pieces(): void
    {
        $product = new \App\Models\Product([
            'pieces_per_pallet' => 36,
            'pallets_per_container' => 20,
        ]);

        $this->assertSame(2160, ProductListing::quantityInPieces(3, 'sell by container', $product));
        $this->assertSame(3, ProductListing::quantityForDisplay(2160, 'sell by container', $product));
        $this->assertSame(3, ProductListing::quantityForDisplay(2160, 'sell by containers', $product));
    }

    public function test_packaging_conversion_requires_product_packaging_values(): void
    {
        $product = new \App\Models\Product();

        $this->assertNull(ProductListing::quantityInPieces(3, 'sell by container', $product));
        $this->assertSame(2160, ProductListing::quantityForDisplay(2160, 'sell by container', $product));
        $this->assertSame(3, ProductListing::quantityInPieces(3, 'sell by pieces', $product));
    }

    public function test_sell_type_label_handles_current_and_legacy_values(): void
    {
        $this->assertSame('Sell By Pieces', ProductListing::sellTypeLabel('sell by pieces'));
        $this->assertSame('Sell By Pallets', ProductListing::sellTypeLabel('sell by pallets'));
        $this->assertSame('Sell By Container', ProductListing::sellTypeLabel('sell by containers'));
        $this->assertSame('N/A', ProductListing::sellTypeLabel(null));
        $this->assertSame('N/A', ProductListing::sellTypeLabel([]));
        $this->assertSame('N/A', ProductListing::sellTypeLabel(new \stdClass()));
    }
}

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
}

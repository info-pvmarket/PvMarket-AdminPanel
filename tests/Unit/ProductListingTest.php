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
        $this->assertNotContains('incoterm_id', $listing->getFillable());
    }
}

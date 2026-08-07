<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MarketHardDeleteTest extends TestCase
{
    public function test_admin_market_delete_is_permanent_for_market_and_owned_configuration(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/MarketController.php');

        $this->assertStringContainsString(
            "MarketDomain::raw(",
            $controller
        );
        $this->assertStringContainsString(
            "MarketSettings::raw(",
            $controller
        );
        $this->assertStringContainsString(
            "->deleteMany(['market_id' => \$marketId])",
            $controller
        );
        $this->assertStringContainsString("Market::raw(", $controller);
        $this->assertStringContainsString("->deleteOne(['_id' => \$marketId])", $controller);
        $this->assertStringContainsString(
            "abort_unless(\$result->getDeletedCount() === 1",
            $controller
        );
        $this->assertStringNotContainsString('$record->delete()', $controller);
        $this->assertStringNotContainsString('$record->forceDelete()', $controller);
    }
}

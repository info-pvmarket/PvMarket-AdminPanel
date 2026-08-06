<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MarketHardDeleteTest extends TestCase
{
    public function test_admin_market_delete_is_permanent_for_market_and_owned_configuration(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/Admin/MarketController.php');

        $this->assertStringContainsString(
            "MarketDomain::withTrashed()->where('market_id', \$marketId)->forceDelete()",
            $controller
        );
        $this->assertStringContainsString(
            "MarketSettings::withTrashed()->where('market_id', \$marketId)->forceDelete()",
            $controller
        );
        $this->assertStringContainsString('$record->forceDelete()', $controller);
        $this->assertStringNotContainsString("MarketDomain::where('market_id', \$marketId)->delete()", $controller);
        $this->assertStringNotContainsString("MarketSettings::where('market_id', \$marketId)->delete()", $controller);
    }
}

<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CommissionPresentationTest extends TestCase
{
    public function test_commission_modes_share_one_admin_layout(): void
    {
        $view = file_get_contents(
            dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views'
            .DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'setup'.DIRECTORY_SEPARATOR
            .'commissions'.DIRECTORY_SEPARATOR.'commissions.blade.php'
        );

        $this->assertSame(1, substr_count($view, "@extends('layouts.admin')"));
        $this->assertSame(1, substr_count($view, '<table class="data-table">'));
        $this->assertStringContainsString("@if(\$mode === 'create')", $view);
        $this->assertStringContainsString("@elseif(\$mode === 'edit')", $view);
    }
}

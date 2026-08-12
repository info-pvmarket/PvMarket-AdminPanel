<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function test_lang_returns_an_empty_string_for_a_missing_related_record(): void
    {
        $this->assertSame('', lang(null, 'name'));
    }
}

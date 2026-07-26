<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SeoMetaPresentationTest extends TestCase
{
    private function projectFile(string $path): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function test_content_field_is_removed_and_bottom_description_uses_quill(): void
    {
        $view = file_get_contents($this->projectFile('resources/views/admin/seo-meta/seo-meta.blade.php'));
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/SeoMetaController.php'));

        $this->assertStringNotContainsString('name="content"', $view);
        $this->assertSame(2, substr_count($view, 'name="bottom_description"'));
        $this->assertSame(2, substr_count($view, 'id="bottomDescriptionEditor"'));
        $this->assertSame(2, substr_count($view, "asset('assets/vendor/quill.min.js')"));
        $this->assertStringNotContainsString('$request->content', $controller);
        $this->assertSame(2, substr_count($controller, "'bottom_description'=> \$request->bottom_description"));
    }
}

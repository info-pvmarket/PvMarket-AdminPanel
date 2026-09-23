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

    public function test_open_graph_and_robot_settings_are_persisted_from_create_and_edit_forms(): void
    {
        $view = file_get_contents($this->projectFile('resources/views/admin/seo-meta/seo-meta.blade.php'));
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/SeoMetaController.php'));

        $this->assertSame(2, substr_count($controller, "'og_type'          => 'required|in:website,article,product'"));
        $this->assertSame(2, substr_count($controller, "SeoOGMetaData::create(\$ogData)"));
        $this->assertStringNotContainsString("filled('og_title') || \$request->filled('og_description')", $controller);

        foreach (['robot_index', 'robot_follow', 'robot_noarchive', 'robot_nosnippet', 'robot_noimageindex', 'robot_nocache'] as $field) {
            $this->assertSame(2, substr_count($view, 'type="hidden" name="' . $field . '" value="0"'));
        }

        $this->assertSame(2, substr_count($controller, "'index'             => \$request->boolean('robot_index')"));
        $this->assertSame(2, substr_count($controller, "'follow'            => \$request->boolean('robot_follow')"));
    }
}

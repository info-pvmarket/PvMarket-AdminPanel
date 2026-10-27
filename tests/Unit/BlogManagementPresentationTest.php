<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class BlogManagementPresentationTest extends TestCase
{
    private function projectFile(string $path): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function test_blog_form_uses_a_bounded_preview_and_responsive_layout(): void
    {
        $view = file_get_contents($this->projectFile('resources/views/admin/knowledge-hub/blogs/blogs.blade.php'));

        $this->assertStringContainsString('.img-preview { width:160px; height:100px;', $view);
        $this->assertStringContainsString('@media (max-width: 900px)', $view);
        $this->assertStringContainsString('Maximum file size: 5 MB.', $view);
    }

    public function test_blog_images_are_limited_to_5_megabytes_on_client_and_server(): void
    {
        $controller = file_get_contents($this->projectFile('app/Http/Controllers/Admin/BlogController.php'));
        $view = file_get_contents($this->projectFile('resources/views/admin/knowledge-hub/blogs/blogs.blade.php'));
        $phpIni = file_get_contents($this->projectFile('docker/php.ini'));

        $this->assertSame(1, substr_count($controller, "'image'           => 'required|image|mimes:jpeg,png,jpg,webp|max:5120'"), 'New blogs require a valid cover with the same 5 MB limit.');
        $this->assertSame(1, substr_count($controller, "'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'"), 'Existing blogs may retain their cover without uploading a replacement.');
        $this->assertSame(2, substr_count($controller, "'image.max' => 'The blog image must not be larger than 5 MB.'"));
        $this->assertStringContainsString('var maxImageSize = 5 * 1024 * 1024;', $view);
        $this->assertStringContainsString('upload_max_filesize=600M', $phpIni);
        $this->assertStringContainsString('post_max_size=610M', $phpIni);
    }
}

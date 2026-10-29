<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\EditorImageController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogImageStorageTest extends TestCase
{
    public function test_blog_editor_uses_r2_while_other_editors_keep_public_storage(): void
    {
        Storage::fake('public');
        Storage::fake('r2');
        $user = new class {
            public function canAccessAdmin() { return true; }
            public function hasAdminPermission($permission) { return true; }
        };
        foreach (['blog' => 'r2', 'news' => 'public'] as $context => $disk) {
            $file = UploadedFile::fake()->createWithContent('chart.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));
            $request = Request::create('/admin/editor-images?context='.$context, 'POST', [], [], ['image' => $file]);
            $request->setUserResolver(fn () => $user);
            $response = app(EditorImageController::class)->store($request);
            $paths = Storage::disk($disk)->allFiles();
            $this->assertCount(1, $paths);
            $this->assertSame(Storage::disk($disk)->url($paths[0]), $response->getData(true)['url']);
        }
    }
}

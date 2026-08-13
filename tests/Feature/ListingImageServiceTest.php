<?php

namespace Tests\Feature;

use App\Services\ListingImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ListingImageServiceTest extends TestCase
{
    public function test_png_uploads_remain_png_and_are_stored_on_r2(): void
    {
        Storage::fake('r2');
        Storage::fake('public');
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true,
        );
        $file = UploadedFile::fake()->createWithContent('transparent.png', $png);

        $image = app(ListingImageService::class)->store($file);

        $this->assertSame('image/png', $image['mime_type']);
        $this->assertStringEndsWith('.png', $image['filename']);
        $this->assertStringEndsWith('.png', $image['path']);
        $this->assertSame('transparent.png', $image['original_name']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $image['checksum_sha256']);
        Storage::disk('r2')->assertExists($image['path']);

        app(ListingImageService::class)->delete($image);

        Storage::disk('r2')->assertMissing($image['path']);
    }

    public function test_legacy_duplicate_metadata_has_the_same_signature(): void
    {
        $service = app(ListingImageService::class);
        $first = [
            'original_name' => 'removebg-preview.png',
            'size' => 218788,
            'mime_type' => 'image/png',
        ];
        $duplicate = $first + ['path' => 'product-listings/another-name.png'];

        $this->assertSame(
            $service->metadataSignature($first),
            $service->metadataSignature($duplicate),
        );
    }
}

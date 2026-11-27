<?php

namespace Tests\Unit;

use App\Support\BlogCoverImage;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

class BlogCoverImageTest extends TestCase
{
    private array $tmp = [];

    protected function tearDown(): void
    {
        foreach ($this->tmp as $f) {
            @unlink($f);
        }
    }

    /** A noisy image (so PNG cannot compress it away), optionally with transparent pixels. */
    private function image(int $w, int $h, string $type, bool $transparent = false, bool $noisy = true, int $block = 2): UploadedFile
    {
        $img = imagecreatetruecolor($w, $h);
        imagealphablending($img, false); // keep transparent pixels as drawn
        imagesavealpha($img, true);
        mt_srand(7);
        for ($y = 0; $y < $h; $y += $block) {
            for ($x = 0; $x < $w; $x += $block) {
                $alpha = $transparent && $x < $w / 4 ? 127 : 0;
                imagefilledrectangle($img, $x, $y, $x + $block - 1, $y + $block - 1, $noisy ? imagecolorallocatealpha($img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255), $alpha) : imagecolorallocatealpha($img, 30, 90, 160, $alpha));
            }
        }
        $path = tempnam(sys_get_temp_dir(), 'cover');
        $this->tmp[] = $path;
        $type === 'png' ? imagepng($img, $path, 6) : imagejpeg($img, $path, 95);
        imagedestroy($img);

        return new UploadedFile($path, "cover.$type", $type === 'png' ? 'image/png' : 'image/jpeg', null, true);
    }

    public function test_a_large_opaque_png_becomes_a_1600px_jpeg_that_is_smaller(): void
    {
        $file = $this->image(1731, 908, 'png');
        $this->assertGreaterThan(BlogCoverImage::MAX_BYTES, $file->getSize());

        $out = BlogCoverImage::shrink($file);

        $this->assertNotNull($out);
        $this->assertSame('image/jpeg', $out['mime']);
        $this->assertSame('cover.jpg', $out['filename']);
        $this->assertLessThan($file->getSize(), $out['size']);
        [$w, $h] = getimagesizefromstring($out['contents']);
        $this->assertSame([1600, 839], [$w, $h], 'aspect ratio kept');
    }

    public function test_a_large_transparent_png_stays_a_png(): void
    {
        $out = BlogCoverImage::shrink($this->image(2400, 1200, 'png', true, true, 24));

        $this->assertNotNull($out);
        $this->assertSame('image/png', $out['mime']);
        $this->assertSame('cover.png', $out['filename']);
        [$w] = getimagesizefromstring($out['contents']);
        $this->assertSame(1600, $w);
    }

    public function test_a_small_cover_is_left_unchanged(): void
    {
        $file = $this->image(1200, 630, 'jpg', false, false);
        $this->assertLessThanOrEqual(BlogCoverImage::MAX_BYTES, $file->getSize());
        $this->assertNull(BlogCoverImage::shrink($file));
    }

    public function test_a_file_that_is_not_an_image_is_left_to_the_normal_upload(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'cover');
        $this->tmp[] = $path;
        file_put_contents($path, str_repeat('not an image', 100000));

        $this->assertNull(BlogCoverImage::shrink(new UploadedFile($path, 'cover.png', 'image/png', null, true)));
    }

    public function test_the_blog_upload_uses_the_shrunk_copy_and_falls_back_to_the_original(): void
    {
        $src = file_get_contents(dirname(__DIR__, 2) . '/app/Http/Controllers/Admin/BlogController.php');

        $this->assertStringContainsString('$shrunk = BlogCoverImage::shrink($file);', $src);
        $this->assertStringContainsString("Storage::disk('r2')->put('blogs/' . \$filename, \$shrunk['contents'])", $src);
        $this->assertStringContainsString("\$path     = \$file->storeAs('blogs', \$filename, 'r2');", $src);
        $this->assertStringContainsString("'mime_type'     => \$shrunk['mime'] ?? \$file->getMimeType(),", $src);
    }
}

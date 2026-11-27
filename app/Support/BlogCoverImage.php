<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Shrinks an oversized blog cover before it is stored.
 *
 * The storefront resizes covers on request; a very large original (e.g. a 1.66 MB,
 * 1731x908 PNG) can take longer than its 7 s limit on the production server, which
 * then shows an empty image box. Covers are shown at most ~1200 px wide, so a
 * 1600 px copy loses nothing visible.
 *
 * Returns null when the file is already small enough or anything goes wrong, so the
 * caller stores the original unchanged and an upload never fails because of this.
 */
class BlogCoverImage
{
    public const MAX_WIDTH = 1600;
    public const MAX_BYTES = 600 * 1024;
    public const JPEG_QUALITY = 82;

    /** @return array{contents: string, filename: string, mime: string, size: int}|null */
    public static function shrink(UploadedFile $file): ?array
    {
        try {
            $info = @getimagesize($file->getRealPath());
            if (!$info) {
                return null;
            }
            [$width, $height, $type] = $info;
            $size = (int) $file->getSize();
            if ($width <= self::MAX_WIDTH && $size <= self::MAX_BYTES) {
                return null;
            }

            $source = match ($type) {
                IMAGETYPE_JPEG => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($file->getRealPath()) : false,
                IMAGETYPE_PNG  => function_exists('imagecreatefrompng') ? @imagecreatefrompng($file->getRealPath()) : false,
                IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file->getRealPath()) : false,
                default        => false,
            };
            if (!$source) {
                return null;
            }

            $newWidth = min($width, self::MAX_WIDTH);
            $newHeight = max(1, (int) round($height * $newWidth / $width));
            $keepAlpha = $type === IMAGETYPE_PNG && self::hasTransparency($source, $width, $height);

            $target = imagecreatetruecolor($newWidth, $newHeight);
            if ($keepAlpha) {
                imagealphablending($target, false);
                imagesavealpha($target, true);
                imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
            } else {
                // A JPEG has no transparency: paint white behind any semi-transparent pixels.
                imagefill($target, 0, 0, imagecolorallocate($target, 255, 255, 255));
            }
            imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            ob_start();
            $ok = $keepAlpha ? imagepng($target, null, 9) : imagejpeg($target, null, self::JPEG_QUALITY);
            $contents = (string) ob_get_clean();
            imagedestroy($source);
            imagedestroy($target);

            // Keep the original if encoding failed or the copy is not actually smaller.
            if (!$ok || $contents === '' || strlen($contents) >= $size) {
                return null;
            }

            $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'blog-cover';

            return [
                'contents' => $contents,
                'filename' => $base . ($keepAlpha ? '.png' : '.jpg'),
                'mime'     => $keepAlpha ? 'image/png' : 'image/jpeg',
                'size'     => strlen($contents),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Samples the image for any pixel that is not fully opaque. */
    private static function hasTransparency($image, int $width, int $height): bool
    {
        if (!imageistruecolor($image)) {
            return imagecolortransparent($image) >= 0;
        }
        $stepX = max(1, intdiv($width, 64));
        $stepY = max(1, intdiv($height, 64));
        for ($y = 0; $y < $height; $y += $stepY) {
            for ($x = 0; $x < $width; $x += $stepX) {
                if (((imagecolorat($image, $x, $y) >> 24) & 0x7F) > 0) {
                    return true;
                }
            }
        }

        return false;
    }
}

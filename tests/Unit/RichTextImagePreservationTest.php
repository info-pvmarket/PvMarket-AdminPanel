<?php

namespace Tests\Unit;

use App\Rules\RichTextImages;
use PHPUnit\Framework\TestCase;

class RichTextImagePreservationTest extends TestCase
{
    public function test_existing_inline_chart_survives_without_allowing_new_inline_images(): void
    {
        $source = 'data:image/png;base64,aGVsbG8=';
        $existing = '<p><img src="'.$source.'" alt="Original chart"></p>';
        $errors = [];
        $fail = static function ($message) use (&$errors) { $errors[] = $message; };
        (new RichTextImages($existing))->validate('data', $existing, $fail);
        self::assertSame([], $errors);

        (new RichTextImages($existing))->validate('data', str_replace('aGVsbG8=', 'bmV3', $existing), $fail);
        self::assertCount(1, $errors);

        $errors = [];
        (new RichTextImages($existing))->validate('data', str_replace(' alt="Original chart"', '', $existing), $fail);
        self::assertCount(1, $errors);
    }

    public function test_new_posts_still_reject_inline_images_and_require_alt_for_uploads(): void
    {
        $errors = [];
        $fail = static function ($message) use (&$errors) { $errors[] = $message; };
        (new RichTextImages)->validate('data', '<img src="data:image/gif;base64,AAAA" alt="chart">', $fail);
        (new RichTextImages)->validate('data', '<img src="/storage/chart.gif">', $fail);
        self::assertCount(2, $errors);
        $errors = [];
        (new RichTextImages)->validate('data', '<img src="/storage/chart.gif" alt="Chart">', $fail);
        self::assertSame([], $errors);
    }

    public function test_legacy_missing_alt_does_not_delete_or_block_existing_chart(): void
    {
        $html = '<p><img src="data:image/gif;base64,AAAA"></p>';
        $errors = [];
        (new RichTextImages($html))->validate('data', $html, static function ($message) use (&$errors) {
            $errors[] = $message;
        });
        self::assertSame([], $errors);
    }
}

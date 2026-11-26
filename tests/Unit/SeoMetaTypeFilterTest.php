<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\SeoMetaController;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * The Type filter must follow where each page really lives: the Homepage is stored
 * with page_key "home" and belongs under "Home", the other managed pages under
 * "Standalone Pages". The queries are built without a database.
 */
class SeoMetaTypeFilterTest extends TestCase
{
    private const MANAGED = ['all_categories', 'brands', 'discount_center', 'request_for_quote', 'home',
        'about', 'contact', 'terms', 'delivery', 'disclaimer', 'privacy', 'customer_support', 'faq'];

    private function mql(array $params = []): string
    {
        $controller = new SeoMetaController($this->createMock(TranslationService::class));
        $method = new \ReflectionMethod($controller, 'indexQuery');
        $method->setAccessible(true);
        $query = $method->invoke($controller, Request::create('/admin/seo-meta', 'GET', $params));

        return json_encode($query->toBase()->toMql());
    }

    private function projectFile(string $path): string
    {
        return file_get_contents(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));
    }

    public function test_home_filter_matches_homepage_records_by_page_key_and_legacy_home_records(): void
    {
        $mql = $this->mql(['type' => 'home']);

        $this->assertStringContainsString('{"page_key":"home"}', $mql, 'finds the stored Homepage');
        // The older kind of home record (no page_key and no entity) is still found.
        $this->assertStringContainsString('"category_id":null', $mql);
        $this->assertStringContainsString('"brand_id":null', $mql);
        $this->assertStringContainsString('"product_id":null', $mql);
        // Either the stored Homepage OR the older record: the page_key is not required to be empty.
        $this->assertStringContainsString('{"$or":[{"page_key":"home"},{"$and":[{"$or":[{"page_key":null},{"page_key":""}]},{"category_id":null}', $mql);
    }

    public function test_standalone_pages_filter_lists_managed_pages_except_the_homepage(): void
    {
        $mql = $this->mql(['type' => 'landing']);

        // The base query also uses the full managed list, so check the filter's own list (the last one).
        preg_match_all('/"page_key":\{"\$in":\[([^\]]*)\]\}/', $mql, $all);
        $lists = array_map(fn ($raw) => json_decode('[' . $raw . ']', true), $all[1]);
        $filterList = end($lists);

        $this->assertNotContains('home', $filterList, 'the Homepage is not a Standalone Page');
        $expected = array_values(array_diff(self::MANAGED, ['home']));
        $this->assertEqualsCanonicalizing($expected, $filterList);
    }

    public function test_other_filters_and_the_unfiltered_list_are_unchanged(): void
    {
        $this->assertStringNotContainsString('"page_key":"home"}', $this->mql(['type' => 'category']));
        $this->assertStringContainsString('"category_id":{"$ne":null}', $this->mql(['type' => 'category']));
        $this->assertStringContainsString('"brand_id":{"$ne":null}', $this->mql(['type' => 'brand']));
        // No type filter: the Homepage is among the managed pages the list always includes.
        $this->assertStringContainsString('"home"', $this->mql());
    }

    public function test_market_filter_still_combines_with_the_home_filter(): void
    {
        $mql = $this->mql(['type' => 'home', 'market_id' => 'global']);
        $this->assertStringContainsString('"market_id":null', $mql);
        $this->assertStringContainsString('"region_code":null', $mql);
        $this->assertStringContainsString('{"page_key":"home"}', $mql);
    }

    public function test_add_form_puts_the_homepage_under_home_and_not_under_site_pages(): void
    {
        $view = $this->projectFile('resources/views/admin/seo-meta/seo-meta.blade.php');

        $this->assertStringContainsString('<optgroup label="Home">', $view);
        $this->assertSame(1, substr_count($view, 'value="staticpage:home"'), 'one Homepage choice, same stored value');
        $this->assertStringContainsString("@continue(\$key === 'home')", $view, 'Site Pages no longer repeats the Homepage');
        $this->assertStringContainsString('Pick <strong>Homepage</strong> under Home', $view);
    }

    public function test_type_badges_only_style_homepages_as_home(): void
    {
        $view = $this->projectFile('resources/views/admin/seo-meta/seo-meta.blade.php');

        $this->assertStringContainsString("'Homepage', 'Global Home', 'Market Home', 'Region Home' => 'home'", $view);
        $this->assertStringContainsString("default => 'page'", $view);
        $this->assertStringContainsString('.page-type-badge.page', $view);
    }
}

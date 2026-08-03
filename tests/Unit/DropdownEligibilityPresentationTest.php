<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class DropdownEligibilityPresentationTest extends TestCase
{
    private function projectFile(string $path): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function test_category_and_subcategory_models_define_dropdown_eligibility(): void
    {
        $category = file_get_contents($this->projectFile('app/Models/MainMenu.php'));
        $subcategory = file_get_contents($this->projectFile('app/Models/SubMenu.php'));

        $this->assertStringContainsString('function scopeAvailableForDropdown', $category);
        $this->assertStringContainsString("->where('is_active', true)", $category);
        $this->assertStringContainsString("->where('stock_value', true)", $category);

        $this->assertStringContainsString('function scopeAvailableForDropdown', $subcategory);
        $this->assertStringContainsString("->where('is_active', true)", $subcategory);
    }

    public function test_admin_dropdown_sources_use_the_shared_eligibility_scopes(): void
    {
        $controllers = [
            'app/Http/Controllers/Admin/CommissionController.php',
            'app/Http/Controllers/Admin/ProductController.php',
            'app/Http/Controllers/Admin/ProductDetailOptionController.php',
            'app/Http/Controllers/Admin/ProductListingController.php',
            'app/Http/Controllers/Admin/SeoMetaController.php',
            'app/Http/Controllers/Admin/SubMenuController.php',
        ];

        foreach ($controllers as $path) {
            $contents = file_get_contents($this->projectFile($path));
            $this->assertStringContainsString('availableForDropdown()', $contents, $path);
        }

        $productListings = file_get_contents($this->projectFile(
            'app/Http/Controllers/Admin/ProductListingController.php'
        ));
        $this->assertStringNotContainsString('MainMenu::all()', $productListings);
        $this->assertStringNotContainsString('SubMenu::all()', $productListings);
    }
}

<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ProductDetailOptionPresentationTest extends TestCase
{
    private function projectFile(string $path): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function test_list_shows_and_filters_category_and_subcategory(): void
    {
        $controller = file_get_contents($this->projectFile(
            'app/Http/Controllers/Admin/ProductDetailOptionController.php'
        ));
        $view = file_get_contents($this->projectFile(
            'resources/views/admin/products/product-detail-options.blade.php'
        ));

        $this->assertStringContainsString("where('name', 'like'", $controller);
        $this->assertStringContainsString("where('category_id', \$categoryId)", $controller);
        $this->assertStringContainsString("where('sub_category_id', \$subCategoryId)", $controller);
        $this->assertStringContainsString('getCategoryDropdownData($categoryId)', $controller);

        $this->assertStringContainsString('name="category_id"', $view);
        $this->assertStringContainsString('name="sub_category_id"', $view);
        $this->assertStringContainsString('<th class="center">Category</th>', $view);
        $this->assertStringContainsString('<th class="center">Subcategory</th>', $view);
        $this->assertStringContainsString("lang(\$option, 'category_name')", $view);
        $this->assertStringContainsString("lang(\$option, 'sub_category_name')", $view);
        $this->assertStringNotContainsString('name="data_type"', $view);
        $this->assertStringNotContainsString('>Data Type</th>', $view);
        $this->assertStringNotContainsString("'data_type'", $controller);
    }

    public function test_product_form_loads_specifications_for_selected_subcategory(): void
    {
        $controller = file_get_contents($this->projectFile(
            'app/Http/Controllers/Admin/ProductController.php'
        ));
        $view = file_get_contents($this->projectFile(
            'resources/views/admin/products/products.blade.php'
        ));

        $this->assertStringContainsString(
            "ProductDetailOption::where('sub_category_id', \$subCategoryId)",
            $controller
        );
        $this->assertStringContainsString("->orderBy('name')", $controller);
        $this->assertStringContainsString("'option_name' => \$option->name", $controller);
        $this->assertStringContainsString("->get(['_id', 'name', 'unit_ids', 'unit_names'])", $controller);

        $this->assertStringContainsString('onchange="handleSubCategoryChange(this.value)"', $view);
        $this->assertStringContainsString("route(\"admin.products.options-by-submenu\")", $view);
        $this->assertStringContainsString('renderDetailsTable(data.options, savedDetails)', $view);
    }
}

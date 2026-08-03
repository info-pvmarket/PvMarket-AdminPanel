<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\ProductController;
use App\Models\ProductDetailOption;
use MongoDB\BSON\ObjectId;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

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

    public function test_product_form_accepts_extended_json_unit_ids_from_existing_records(): void
    {
        $reflection = new ReflectionClass(ProductController::class);
        $controller = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('toObjectId');
        $id = '69fddba45f1a124969006952';

        foreach ([
            $id,
            new ObjectId($id),
            ['$oid' => $id],
            [['$oid' => $id]],
            '[{"$oid":"'.$id.'"}]',
        ] as $value) {
            $this->assertSame($id, (string) $method->invoke($controller, $value));
        }
    }

    public function test_product_form_normalizes_native_and_json_encoded_unit_lists(): void
    {
        $reflection = new ReflectionClass(ProductController::class);
        $controller = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('normalizeList');
        $id = '69fddba45f1a124969006952';

        $this->assertSame(
            [['$oid' => $id]],
            $method->invoke($controller, '[{"$oid":"'.$id.'"}]'),
        );
        $this->assertSame(['kWh'], $method->invoke($controller, '["kWh"]'));
        $this->assertSame(['kWh'], $method->invoke($controller, ['kWh']));
    }

    public function test_specification_unit_arrays_remain_native_for_mongodb(): void
    {
        $model = new ProductDetailOption();
        $model->unit_ids = [new ObjectId('69fddba45f1a124969006952')];
        $model->unit_names = ['kWh'];

        $this->assertIsArray($model->getAttributes()['unit_ids']);
        $this->assertIsArray($model->getAttributes()['unit_names']);
    }

    public function test_specification_reads_legacy_json_unit_arrays(): void
    {
        $id = '69fddba45f1a124969006952';
        $model = new ProductDetailOption();
        $model->setRawAttributes([
            'unit_ids' => '[{"$oid":"'.$id.'"}]',
            'unit_names' => '["kWh"]',
        ]);

        $this->assertSame([['$oid' => $id]], $model->unit_ids);
        $this->assertSame(['kWh'], $model->unit_names);
    }
}

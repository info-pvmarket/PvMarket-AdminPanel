<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\MainMenu;
use App\Models\Product;
use App\Models\ProductDetailOption;
use App\Models\ProductImportBatch;
use App\Models\SubMenu;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use MongoDB\BSON\ObjectId;

class ProductImport implements ToCollection, WithHeadingRow
{
    public function __construct(
        public string $batchId,
        public string $categoryId,
        public string $subCategoryId,
        public string $userId,
        public bool $verifyOnCreate,
    ) {}

    public function collection(Collection $rows): void
    {
        $category    = MainMenu::find($this->categoryId);
        $subCategory = SubMenu::find($this->subCategoryId);
        $catName     = $category?->category_name;
        $subName     = $subCategory?->sub_category_name;

        // Dynamic spec columns: map slugged header -> canonical spec name.
        $specBySlug = [];
        foreach (ProductDetailOption::where('sub_category_id', new ObjectId($this->subCategoryId))->get() as $sp) {
            $specBySlug[Str::slug((string) $sp->name, '_')] = (string) $sp->name;
        }

        // Lookups (per chunk).
        $brandByName = [];
        foreach (Brand::where('is_active', true)->get() as $b) {
            if ($b->name) $brandByName[mb_strtolower(trim((string) $b->name))] = $b;
        }
        $unitByKey = [];
        foreach (Unit::where('is_active', true)->get() as $u) {
            if ($u->unit_name) $unitByKey[mb_strtolower(trim((string) $u->unit_name))] = $u;
            if ($u->unit_code) $unitByKey[mb_strtolower(trim((string) $u->unit_code))] = $u;
        }

        foreach ($rows as $row) {
            $r = $this->withFieldAliases($row->toArray());

            $name          = trim((string) ($r['product_name'] ?? ''));
            $specificValue = trim((string) ($r['specific_value'] ?? ''));

            // Skip fully blank rows.
            if ($name === '' && $specificValue === '' && trim(implode('', array_map('strval', $r))) === '') {
                continue;
            }

            try {
                if ($name === '') { $this->rowError('', 'product_name', 'Product name is required'); continue; }
                if ($specificValue === '') { $this->rowError($name, 'specific_value', 'Specific value is required'); continue; }
                if (Product::where('product_name', $name)->exists()) {
                    $this->rowError($name, 'product_name', 'A product with this name already exists');
                    continue;
                }

                // Brand (optional).
                $brandName = trim((string) ($r['brand_name'] ?? ''));
                $brand = $brandName !== '' ? ($brandByName[mb_strtolower($brandName)] ?? null) : null;
                if ($brandName !== '' && !$brand) { $this->rowError($name, 'brand_name', "Unknown brand \"$brandName\""); continue; }

                // Specific value unit (optional).
                $unitInput = trim((string) ($r['specific_value_unit'] ?? ''));
                $unit = $unitInput !== '' ? ($unitByKey[mb_strtolower($unitInput)] ?? null) : null;
                if ($unitInput !== '' && !$unit) { $this->rowError($name, 'specific_value_unit', "Unknown unit \"$unitInput\""); continue; }

                // Dynamic specs -> product_details.
                $details = [];
                foreach ($specBySlug as $slug => $specName) {
                    $v = trim((string) ($r[$slug] ?? ''));
                    if ($v !== '') $details[] = ['label' => $specName, 'value' => $v, 'unit' => null];
                }

                $num = fn ($v) => ($v !== null && trim((string) $v) !== '') ? (float) $v : null;

                $data = [
                    'product_name'             => $name,
                    'product_description'      => trim((string) ($r['product_description'] ?? '')) ?: null,
                    'specific_value'           => $specificValue,
                    'specific_value_unit_id'   => $unit ? new ObjectId((string) $unit->_id) : null,
                    'specific_value_unit_name' => $unit?->unit_name,
                    'specific_value_unit_code' => $unit?->unit_code,
                    'category_id'              => new ObjectId($this->categoryId),
                    'category_name'            => $catName,
                    'sub_category_id'          => new ObjectId($this->subCategoryId),
                    'sub_category_name'        => $subName,
                    'brand_id'                 => $brand ? new ObjectId((string) $brand->_id) : null,
                    'brand_name'               => $brand?->name,
                    'pieces_per_pallet'        => trim((string) ($r['pieces_per_pallet'] ?? '')) ?: null,
                    'pallets_per_container'    => trim((string) ($r['pallets_per_container'] ?? '')) ?: null,
                    'product_details'          => $details,
                    'measurement_details'      => [
                        'height' => $num($r['height'] ?? null), 'height_unit' => $r['height_unit'] ?? null,
                        'width'  => $num($r['width'] ?? null),  'width_unit'  => $r['width_unit'] ?? null,
                        'depth'  => $num($r['depth'] ?? null),  'depth_unit'  => $r['depth_unit'] ?? null,
                        'weight' => $num($r['weight'] ?? null), 'weight_unit' => $r['weight_unit'] ?? null,
                    ],
                    'verification_status'      => $this->verifyOnCreate ? 'verified' : 'pending',
                    'sku_code'                 => $this->generateSku($catName ?? 'PRD'),
                    'created_by'               => new ObjectId($this->userId),
                    'updated_by'               => new ObjectId($this->userId),
                ];

                Product::create($data);
                ProductImportBatch::where('_id', $this->batchId)->increment('success_count');
            } catch (\Throwable $e) {
                $this->rowError($name, null, $e->getMessage());
            }
        }
    }

     // The template uses website labels, which arrive slugged ("Brand" -> "brand").
    // Map them onto the field names used above; old templates still work.
    private const FIELD_ALIASES = [
        'brand'         => 'brand_name',
        'main_category' => 'category_name',
        'sub_category'  => 'sub_category_name',
        'specification' => 'specific_value',
        'unit'          => 'specific_value_unit',
    ];

    private function withFieldAliases(array $r): array
    {
        foreach (self::FIELD_ALIASES as $from => $to) {
            if (array_key_exists($from, $r) && ! array_key_exists($to, $r)) {
                $r[$to] = $r[$from];
            }
        }
        return $r;
    }


    private function rowError(string $product, ?string $column, string $message): void
    {
        ProductImportBatch::where('_id', $this->batchId)->increment('error_count');
        ProductImportBatch::where('_id', $this->batchId)
            ->push('errors', ['product' => $product, 'column' => $column, 'message' => $message]);
    }

    private function generateSku(string $categoryName): string
    {
        $prefix = '';
        foreach (preg_split('/\s+/', trim($categoryName)) as $word) {
            $prefix .= strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $word), 0, 1));
        }
        $prefix = substr($prefix, 0, 3) ?: 'PRD';
        return "{$prefix}-" . now()->format('YmdHis') . '-' . str_pad((string) rand(0, 999), 3, '0', STR_PAD_LEFT);
    }
}
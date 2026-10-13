<?php 

namespace App\Imports;

use App\Models\Incoterm;
use App\Models\ListingImportBatch;
use App\Models\Product;
use App\Models\ProductListing;
use App\Models\Warehouse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use MongoDB\BSON\ObjectId;

class ListingImport implements ToCollection, WithHeadingRow
{
    public function __construct(
        public string $batchId,
        public string $userId,
    ) {}

    public function collection(Collection $rows): void
    {
        // Lookups (per chunk).
        $productsByName = [];
        foreach (Product::all() as $p) {
            if ($p->product_name) $productsByName[mb_strtolower(trim($p->product_name))][] = $p;
        }
        $warehouseByName = [];
        foreach (Warehouse::all() as $w) {
            if ($w->warehouse_name) $warehouseByName[mb_strtolower(trim($w->warehouse_name))] = $w;
        }
        $incotermByKey = [];
        foreach (Incoterm::all() as $i) {
            if ($i->code) $incotermByKey[mb_strtolower(trim($i->code))] = $i;
            if ($i->name) $incotermByKey[mb_strtolower(trim($i->name))] = $i;
        }

        $validSell = ['sell by pieces' => true, 'sell by pallets' => true, 'sell by container' => true];

        foreach ($rows as $row) {
            $r = $row->toArray();
            if (trim(implode('', array_map('strval', $r))) === '') continue;

            $name = trim((string) ($r['product_name'] ?? ''));
            try {
                if ($name === '') { $this->err('', 'product_name', 'Product name is required'); continue; }

                $sheetCat = trim((string) ($r['category'] ?? ''));
                $sheetSub = trim((string) ($r['sub_category'] ?? ''));

                // Resolve product by name, disambiguate by category/sub.
                $cands = $productsByName[mb_strtolower($name)] ?? [];
                if (count($cands) === 0) { $this->err($name, 'product_name', "Unknown product \"$name\""); continue; }
                $product = null;
                if (count($cands) === 1) {
                    $product = $cands[0];
                } else {
                    $matched = array_values(array_filter($cands, fn ($p) =>
                        ($sheetCat === '' || strcasecmp((string) $p->category_name, $sheetCat) === 0) &&
                        ($sheetSub === '' || strcasecmp((string) $p->sub_category_name, $sheetSub) === 0)));
                    if (count($matched) === 1) { $product = $matched[0]; }
                    else { $this->err($name, 'product_name', "Multiple products named \"$name\" — set category/sub_category to disambiguate"); continue; }
                }
                if (!$product->category_id || !$product->sub_category_id) { $this->err($name, 'product_name', 'Product is missing category/sub-category'); continue; }
                if ($sheetCat !== '' && strcasecmp((string) $product->category_name, $sheetCat) !== 0) { $this->err($name, 'category', "Category doesn't match the product's category"); continue; }
                if ($sheetSub !== '' && strcasecmp((string) $product->sub_category_name, $sheetSub) !== 0) { $this->err($name, 'sub_category', "Sub-category doesn't match the product's sub-category"); continue; }

                // Warehouse.
                $whName = trim((string) ($r['warehouse_name'] ?? ''));
                $warehouse = $whName !== '' ? ($warehouseByName[mb_strtolower($whName)] ?? null) : null;
                if (!$warehouse) { $this->err($name, 'warehouse_name', "Unknown warehouse \"$whName\""); continue; }

                // Incoterm (code or name).
                $itInput = trim((string) ($r['incoterm'] ?? ''));
                $incoterm = $itInput !== '' ? ($incotermByKey[mb_strtolower($itInput)] ?? null) : null;
                if (!$incoterm) { $this->err($name, 'incoterm', "Unknown incoterm \"$itInput\""); continue; }

                // Currency / sell type / promotion.
                $currency = strtoupper(trim((string) ($r['currency'] ?? '')));
                if ($currency === '') { $this->err($name, 'currency', 'Currency is required'); continue; }
                $sellTypeRaw = strtolower(trim((string) ($r['sell_type'] ?? '')));
                if (!isset($validSell[$sellTypeRaw])) { $this->err($name, 'sell_type', 'Must be: sell by pieces, sell by pallets, or sell by container'); continue; }
                $promotion = trim((string) ($r['promotion'] ?? ''));

                // Quantities.
                $totalQtyRaw = (int) ($r['total_quantity'] ?? 0);
                if ($totalQtyRaw < 1) { $this->err($name, 'total_quantity', 'Must be a whole number ≥ 1'); continue; }
                $leadTime = (int) ($r['lead_time'] ?? 0);
                if ($leadTime < 1) { $this->err($name, 'lead_time', 'Must be a whole number ≥ 1'); continue; }
                if ($limErr = $this->qtyLimit($totalQtyRaw, $sellTypeRaw)) { $this->err($name, 'total_quantity', $limErr); continue; }

                // Tiers (at least one required).
                $slots = $this->parseTiers($r);
                if (count($slots) === 0) { $this->err($name, 'tier1_price', 'At least one price tier is required'); continue; }

                // Convert quantity to pieces.
                $sellType = ProductListing::normalizeSellType($sellTypeRaw);
                $pieces = ProductListing::quantityInPieces($totalQtyRaw, $sellType, $product);
                if ($pieces === null) { $this->err($name, 'sell_type', 'Product missing pieces_per_pallet/pallets_per_container for this sell type'); continue; }

                ProductListing::create([
                    'product_id'          => new ObjectId((string) $product->_id),
                    'main_category_id'    => new ObjectId((string) $product->category_id),
                    'sub_category_id'     => new ObjectId((string) $product->sub_category_id),
                    'warehouse_id'        => new ObjectId((string) $warehouse->_id),
                    'sell_type'           => $sellType,
                    'currency_id'         => $currency,
                    'discount_type'       => $promotion !== '' ? $promotion : null,
                    'total_quantity'      => $pieces,
                    'lead_time'           => $leadTime,
                    'incoterms_id'        => new ObjectId((string) $incoterm->_id),
                    'slug'                => Str::slug($product->product_name . '-' . uniqid()),
                    'slots'               => $slots,
                    'verification_status' => 'pending',
                    'is_active'           => true,
                    'is_paid'             => false,
                    'is_sold_off'         => false,
                    'is_popular'          => false,
                    'is_solar_listing'    => false,
                    'solar_tier'          => null,
                    'solar_grid_types'    => [],
                    'solar_phase_types'   => [],
                    'real_time_price'     => false,
                    'sku_code'            => 'PV-' . rand(1000000000, 9999999999) . '-' . rand(1000, 9999),
                    'user_id'             => new ObjectId($this->userId),
                    'created_by'          => new ObjectId($this->userId),
                ]);
                ListingImportBatch::where('_id', $this->batchId)->increment('success_count');
            } catch (\Throwable $e) {
                $this->err($name, null, $e->getMessage());
            }
        }
    }


    private function parseTiers(array $r): array
    {
        $slots = [];
        foreach ([1, 2, 3] as $n) {
            $min = $r["tier{$n}_min_qty"] ?? '';
            $max = $r["tier{$n}_max_qty"] ?? '';
            $price = $r["tier{$n}_price"] ?? '';
            $com = $r["tier{$n}_commission"] ?? '';
            if (trim((string) $min) . trim((string) $max) . trim((string) $price) . trim((string) $com) === '') continue;
            $p = (float) $price;
            if ($p <= 0) continue;
            $c = $com !== '' ? (float) $com : 0;
            $slots[] = [
                'min_quantity'          => (int) $min,
                'max_quantity'          => trim((string) $max) !== '' ? (int) $max : null,
                'price'                 => $p,
                'commission_percentage' => $c,
                'total_price'           => round($p + ($p * $c / 100), 2),
            ];
        }
        return $slots;
    }

    private function qtyLimit(int $qty, string $sellType): ?string
    {
        [$max, $unit] = match ($sellType) {
            'sell by pieces'    => [15000, 'pieces'],
            'sell by pallets'   => [450, 'pallets'],
            'sell by container' => [20, 'containers'],
            default             => [0, ''],
        };
        if ($max && $qty > $max) return "Total quantity must be ≤ $max when selling by $unit";
        return null;
    }

    private function err(string $product, ?string $column, string $message): void
    {
        ListingImportBatch::where('_id', $this->batchId)->increment('error_count');
        ListingImportBatch::where('_id', $this->batchId)->push('errors', ['product' => $product, 'column' => $column, 'message' => $message]);
    }
}
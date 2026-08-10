<?php

namespace App\Services;

use App\Models\Country;
use App\Models\MainMenu;
use App\Models\Product;
use App\Models\SubMenu;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Collection;
use MongoDB\BSON\ObjectId;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProductListingCsvExporter
{
    public const HEADERS = [
        'S.No',
        'Listing SKU',
        'Created User Name',
        'Created User Email',
        'Created User Phone',
        'Product SKU',
        'Product Name',
        'Brand',
        'Category',
        'Sub Category',
        'Pieces Per Pallet',
        'Pallets Per Container',
        'Warehouse Name',
        'Warehouse Country',
        'is_realtime_price',
        'Sell Type',
        'Discount Type',
        'Currency',
        'Total Quantity',
        'Verification Status',
        'Payment Status',
        'Listing Status',
        'Sold Off',
        'Tier 1',
        'Tier 2',
        'Tier 3',
        'Created At',
        'Updated At',
    ];

    public function download(
        Collection $listings,
        string $filenamePrefix = 'listings_export'
    ): StreamedResponse {
        $productIds = $listings->pluck('product_id')->filter()->unique()
            ->map(fn ($id) => (string) $id)
            ->values();
        $warehouseIds = $listings->pluck('warehouse_id')->filter()->unique()
            ->map(fn ($id) => (string) $id)
            ->values();
        $categoryIds = $listings->pluck('main_category_id')->filter()->unique()
            ->map(fn ($id) => (string) $id)
            ->values();
        $subCategoryIds = $listings->pluck('sub_category_id')->filter()->unique()
            ->map(fn ($id) => (string) $id)
            ->values();
        $userIds = $listings
            ->flatMap(fn ($listing) => [$listing->created_by ?? null, $listing->user_id ?? null])
            ->filter()
            ->unique(fn ($id) => is_object($id) ? get_class($id).':'.(string) $id : 'string:'.(string) $id)
            ->map(fn ($id) => (string) $id)
            ->values();

        $productsMap = Product::whereIn('_id', $productIds)->get()
            ->keyBy(fn ($product) => (string) $product->_id);
        $warehousesMap = Warehouse::whereIn('_id', $warehouseIds)->get()
            ->keyBy(fn ($warehouse) => (string) $warehouse->_id);
        $countryIds = $warehousesMap
            ->pluck('country')
            ->filter()
            ->flatMap(fn ($id) => $this->mongoIdCandidates($id))
            ->unique(fn ($id) => is_object($id) ? get_class($id).':'.(string) $id : 'string:'.$id)
            ->values()
            ->all();
        $countriesMap = Country::whereIn('_id', $countryIds)->get()
            ->keyBy(fn ($country) => (string) $country->_id);
        $categoriesMap = MainMenu::whereIn('_id', $categoryIds)->get()
            ->keyBy(fn ($category) => (string) $category->_id);
        $subCategoriesMap = SubMenu::whereIn('_id', $subCategoryIds)->get()
            ->keyBy(fn ($subCategory) => (string) $subCategory->_id);
        $usersMap = User::whereIn('_id', $userIds)->get()
            ->keyBy(fn ($user) => (string) $user->_id);

        $filename = $filenamePrefix.'_'.now()->format('Y-m-d_His').'.csv';

        return response()->stream(
            function () use (
                $listings,
                $productsMap,
                $warehousesMap,
                $countriesMap,
                $categoriesMap,
                $subCategoriesMap,
                $usersMap
            ) {
                $handle = fopen('php://output', 'w');
                fwrite($handle, "\xEF\xBB\xBF");
                fputcsv($handle, self::HEADERS);

                foreach ($listings as $index => $listing) {
                    $productId = (string) ($listing->product_id ?? '');
                    $warehouseId = (string) ($listing->warehouse_id ?? '');
                    $categoryId = (string) ($listing->main_category_id ?? '');
                    $subCategoryId = (string) ($listing->sub_category_id ?? '');
                    $createdUserId = (string) ($listing->created_by ?? '');

                    if ($createdUserId === '') {
                        $createdUserId = (string) ($listing->user_id ?? '');
                    }

                    $product = $productsMap->get($productId);
                    $warehouse = $warehousesMap->get($warehouseId);
                    $category = $categoriesMap->get($categoryId);
                    $subCategory = $subCategoriesMap->get($subCategoryId);
                    $createdUser = $usersMap->get($createdUserId);
                    $slots = $listing->slots ?? [];
                    $warehouseCountryId = (string) ($warehouse->country ?? '');
                    $country = $countriesMap->get($warehouseCountryId);
                    $warehouseCountryName = (string) ($warehouse->country_name ?? '');

                    if ($warehouseCountryName === '' || $this->isMongoId($warehouseCountryName)) {
                        $warehouseCountryName = $country->name
                            ?? ($this->isMongoId($warehouseCountryId) ? '' : $warehouseCountryId);
                    }

                    fputcsv($handle, [
                        $index + 1,
                        $listing->sku_code ?? '',
                        $createdUser->name ?? '',
                        $createdUser->email ?? '',
                        $createdUser->mobile ?? $createdUser->phone ?? '',
                        $product->sku_code ?? '',
                        $product->product_name ?? '',
                        $product->brand_name ?? $listing->brand_name ?? '',
                        $category->category_name ?? '',
                        $subCategory->sub_category_name ?? '',
                        $product->pieces_per_pallet ?? '',
                        $product->pallets_per_container ?? '',
                        $warehouse->warehouse_name ?? $warehouse->name ?? '',
                        $warehouseCountryName,
                        ($listing->real_time_price ?? false) ? 'Yes' : 'No',
                        $listing->sell_type ?? '',
                        $listing->discount_type ?? '',
                        $listing->currency_id ?? '',
                        $listing->total_quantity ?? '',
                        $listing->verification_status ?? '',
                        ($listing->is_paid ?? false) ? 'Paid' : 'Unpaid',
                        ($listing->is_active ?? false) ? 'Active' : 'On Hold',
                        ($listing->is_sold_off ?? false) ? 'Yes' : 'No',
                        $this->slotPrice($slots[0] ?? null),
                        $this->slotPrice($slots[1] ?? null),
                        $this->slotPrice($slots[2] ?? null),
                        $this->formatDate($listing->created_at ?? null),
                        $this->formatDate($listing->updated_at ?? null),
                    ]);
                }

                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ]
        );
    }

    private function formatDate(mixed $value): string
    {
        if (! $value) {
            return '';
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i');
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i');
        } catch (Throwable) {
            return (string) $value;
        }
    }

    private function slotPrice(mixed $slot): mixed
    {
        $totalPrice = data_get($slot, 'total_price');

        return $totalPrice !== null && $totalPrice !== ''
            ? $totalPrice
            : data_get($slot, 'price', '');
    }

    private function mongoIdCandidates(mixed $id): array
    {
        $stringId = (string) $id;

        if ($stringId === '') {
            return [];
        }

        $candidates = [$stringId];

        if ($this->isMongoId($stringId)) {
            $candidates[] = new ObjectId($stringId);
        }

        return $candidates;
    }

    private function isMongoId(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[a-f\d]{24}$/i', $value);
    }
}

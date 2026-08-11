<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Collection;
use MongoDB\BSON\ObjectId;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProductCsvExporter
{
    public const HEADERS = [
        'S.No',
        'SKU Code',
        'Product Name',
        'Product Description',
        'Product Badge',
        'Badge Unit',
        'Badge Unit Code',
        'Brand',
        'Category',
        'Subcategory',
        'Pieces Per Pallet',
        'Pallets Per Container',
        'Verification Status',
        'Active',
        'Popular',
        'Real-Time Price',
        'Created User Name',
        'Created User Email',
        'Created User Phone',
        'Updated By',
        'Datasheet URL',
        'Created At',
        'Updated At',
    ];

    public function download(Collection $products): StreamedResponse
    {
        $userIds = $products
            ->flatMap(fn ($product) => [$product->created_by ?? null, $product->updated_by ?? null])
            ->filter(fn ($id) => preg_match('/^[a-f\d]{24}$/i', (string) $id))
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->map(fn ($id) => new ObjectId($id))
            ->values()
            ->all();

        $users = empty($userIds)
            ? collect()
            : User::whereIn('_id', $userIds)->get(['_id', 'name', 'email', 'mobile', 'phone'])
                ->keyBy(fn ($user) => (string) $user->_id);
        $filename = 'products_export_'.now()->format('Y-m-d_His').'.csv';

        return response()->stream(
            function () use ($products, $users) {
                $handle = fopen('php://output', 'w');
                fwrite($handle, "\xEF\xBB\xBF");
                fputcsv($handle, self::HEADERS);

                foreach ($products as $index => $product) {
                    $creator = $users->get((string) ($product->created_by ?? ''));
                    $updatedById = (string) ($product->updated_by ?? '');
                    $updatedBy = $users->get($updatedById)?->name
                        ?? ($this->isMongoId($updatedById) ? '' : $updatedById);

                    fputcsv($handle, [
                        $index + 1,
                        $product->sku_code ?? '',
                        $product->product_name ?? '',
                        $this->plainText($product->product_description ?? ''),
                        $product->specific_value ?? '',
                        $product->specific_value_unit_name ?? '',
                        $product->specific_value_unit_code ?? '',
                        $product->brand_name ?? '',
                        $product->category_name ?? '',
                        $product->sub_category_name ?? '',
                        $product->pieces_per_pallet ?? '',
                        $product->pallets_per_container ?? '',
                        $product->verification_status ?? 'pending',
                        $this->yesNo($product->is_active ?? false),
                        $this->yesNo($product->is_popular ?? false),
                        $this->yesNo($product->real_time_price ?? false),
                        $creator->name ?? '',
                        $creator->email ?? '',
                        $creator->mobile ?? $creator->phone ?? '',
                        $updatedBy,
                        data_get($product, 'datasheet.url', ''),
                        $this->formatDate($product->created_at ?? null),
                        $this->formatDate($product->updated_at ?? null),
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

    private function plainText(mixed $value): string
    {
        return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $value))));
    }

    private function yesNo(mixed $value): string
    {
        return filter_var($value, FILTER_VALIDATE_BOOL) ? 'Yes' : 'No';
    }

    private function formatDate(mixed $value): string
    {
        if (!$value) {
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

    private function isMongoId(string $value): bool
    {
        return preg_match('/^[a-f\d]{24}$/i', $value) === 1;
    }
}

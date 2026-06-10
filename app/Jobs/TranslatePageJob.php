<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\TranslationService;

class TranslatePageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 3;

    public function __construct(
        public string $language,
        public string $page
    ) {}

    public function handle(TranslationService $translator): void
    {
        $modelClass = $this->resolveModel($this->page);

        if (!$modelClass || !class_exists($modelClass)) {
            Log::warning("TranslatePageJob: no model mapped for page '{$this->page}'");
            return;
        }

        $model    = new $modelClass;
        $fields   = $model->translatable ?? [];
        $locale   = $this->language;

        if (empty($fields)) {
            Log::info("TranslatePageJob: no translatable fields on {$modelClass}");
            return;
        }

        // Stream records in chunks to avoid memory issues
        $modelClass::chunk(50, function ($records) use ($translator, $fields, $locale, $modelClass) {
            foreach ($records as $record) {
                $existing    = is_array($record->{$locale}) ? $record->{$locale} : [];
                $needsSave   = false;

                foreach ($fields as $field) {
    // Skip if already translated
    if (!empty($existing[$field])) {
        continue;
    }

    $raw = $record->{$field} ?? null;

    // ── Array fields (e.g. unit_names) ──
    if (is_array($raw)) {
        $translatedArray = [];
        $arrayChanged    = false;

        foreach ($raw as $item) {
            if (empty(trim((string) $item))) {
                $translatedArray[] = $item;
                continue;
            }
            try {
                $result = $translator->translateText((string) $item, $locale, 'en');
                $translatedArray[] = ($result && $result !== $item) ? $result : $item;
                if ($result && $result !== $item) $arrayChanged = true;
                usleep(150000);
            } catch (\Exception $e) {
                Log::error("TranslatePageJob: array field '{$field}' on {$modelClass}#{$record->_id}: {$e->getMessage()}");
                $translatedArray[] = $item;
            }
        }

        if ($arrayChanged) {
            $existing[$field] = $translatedArray;
            $needsSave = true;
        }
        continue;
    }

    // ── String fields ──
    $original = (string) ($raw ?? '');
    if (empty(trim($original))) continue;

    try {
        $translated = $translator->translateText($original, $locale, 'en');

        if ($translated && $translated !== $original) {
            $existing[$field] = $translated;
            $needsSave = true;
        }

        usleep(150000);

    } catch (\Exception $e) {
        Log::error("TranslatePageJob: field '{$field}' on {$modelClass}#{$record->_id}: {$e->getMessage()}");
    }
}

                if ($needsSave) {
                    try {
                        // Write directly — same pattern as HasTranslations trait
                        $record->newQuery()
                               ->where('_id', $record->_id)
                               ->update([$locale => $existing]);

                    } catch (\Exception $e) {
                        Log::error("TranslatePageJob: save failed for {$modelClass}#{$record->_id}: {$e->getMessage()}");
                    }
                }
            }
        });

        Log::info("TranslatePageJob: finished page='{$this->page}' lang='{$locale}'");
    }

    /**
     * Map page IDs (matching TM_PAGES in JS) to their Eloquent model class.
     * Add every model your app has here.
     */
    private function resolveModel(string $page): ?string
    {
        return match ($page) {
            'categories'       => \App\Models\MainMenu::class,
            'sub-categories'   => \App\Models\SubMenu::class,
            'brands'           => \App\Models\Brand::class,
            'units'            => \App\Models\Unit::class,
            'locations'        => \App\Models\Location::class,
            'sliders'          => \App\Models\Slider::class,
            'advertisements'   => \App\Models\Advertisement::class,
            'charges'          => \App\Models\Charge::class,
            'commissions'      => \App\Models\Commission::class,
            'countries'        => \App\Models\Country::class,
            'coupons'          => \App\Models\Coupon::class,
            'incoterms'        => \App\Models\Incoterm::class,
            'sub-admins'       => \App\Models\SubAdmin::class,
            'news'             => \App\Models\News::class,
            'events'           => \App\Models\Event::class,
            'blogs'            => \App\Models\Blog::class,
            'price-promotions' => \App\Models\PricePromotion::class,
            'pv-spot-price'    => \App\Models\PvSpotPrice::class,
            'products'         => \App\Models\Product::class,
            'specifications'  => \App\Models\ProductDetailOption::class,
            'static-pages'     => \App\Models\PageSection::class,
            'offers'           => \App\Models\Offer::class,
            'warehouses'       => \App\Models\Warehouse::class,
            'manage-listings'  => \App\Models\ProductListing::class,
            'product-listings'  => \App\Models\ProductListing::class,
            'product'              => \App\Models\Product::class,
            'roles'                => \App\Models\Role::class,
            'inventory-transactions' => \App\Models\InventoryTransaction::class,
            default            => null,
        };
    }
}
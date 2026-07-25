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

    public int $timeout = 1800; // 30 minutes
    public int $tries   = 3;

    /**
     * Page identifiers exposed by the language setup screen.
     *
     * Keeping this map in one place prevents the UI from queueing jobs that can
     * never translate anything (for example, the former sales/leads entries).
     *
     * @var array<string, class-string>
     */
    public const PAGE_MODELS = [
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
        'roles'            => \App\Models\Role::class,
        'news'             => \App\Models\News::class,
        'events'           => \App\Models\Event::class,
        'blogs'            => \App\Models\Blog::class,
        'price-promotions' => \App\Models\PricePromotion::class,
        'pv-spot-price'    => \App\Models\PvSpotPrice::class,
        'products'         => \App\Models\Product::class,
        'specifications'   => \App\Models\ProductDetailOption::class,
        'static-pages'     => \App\Models\PageSection::class,
        'offers'           => \App\Models\Offer::class,
        'warehouses'       => \App\Models\Warehouse::class,
        'manage-listings'  => \App\Models\ProductListing::class,
        'inventory'        => \App\Models\InventoryTransaction::class,
        'bids'             => \App\Models\BidRequest::class,
        'schedules'        => \App\Models\Schedule::class,
        'users'            => \App\Models\User::class,
    ];

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
        $totalCount = $modelClass::count();
        $processed = 0;

        $modelClass::chunk(50, function ($records) use ($translator, $fields, $locale, $modelClass, $totalCount, &$processed) {
            foreach ($records as $record) {
                $processed++;
                Log::info("TranslatePageJob: processing record {$processed}/{$totalCount} for {$modelClass}");
                // Handle both array and object (MongoDB returns objects)
                $localeData = $record->{$locale} ?? null;
                if (is_object($localeData) && method_exists($localeData, 'getArrayCopy')) {
                    $existing = $localeData->getArrayCopy();
                } elseif (is_object($localeData)) {
                    $existing = (array) $localeData;
                } elseif (is_array($localeData)) {
                    $existing = $localeData;
                } else {
                    $existing = [];
                }
                $needsSave   = false;

                foreach ($fields as $field) {

    $raw = $record->{$field} ?? null;

    // Convert MongoDB objects to arrays
    if (is_object($raw) && method_exists($raw, 'getArrayCopy')) {
        $raw = $raw->getArrayCopy();
    } elseif (is_object($raw)) {
        $raw = (array) $raw;
    }

    // ── Array of objects (e.g. product_details: [{label, value, unit}, ...]) ──
    if (is_array($raw) && !empty($raw)) {
        $firstItem = reset($raw);
        $isArrayOfObjects = is_array($firstItem) || is_object($firstItem);
    } else {
        $isArrayOfObjects = false;
    }

    if ($isArrayOfObjects) {
        Log::debug("TranslatePageJob: translating array of objects '{$field}' on {$modelClass}#{$record->_id}");
        $translatedArray = [];
        $arrayChanged    = false;

        foreach ($raw as $item) {
            $itemArray = (array) $item;
            $translatedItem = $itemArray;

            // Translate 'label' field if present
            if (!empty($itemArray['label']) && is_string($itemArray['label'])) {
                try {
                    $result = $translator->translateText($itemArray['label'], $locale, 'en', true);
                    if ($result !== null) {
                        $translatedItem['label'] = $result;
                        $arrayChanged = true;
                    }
                    usleep(50000);
                } catch (\Exception $e) {
                    Log::error("TranslatePageJob: array object 'label' on {$modelClass}#{$record->_id}: {$e->getMessage()}");
                }
            }

            // Translate 'value' field if present and is a string (not numeric)
            if (!empty($itemArray['value']) && is_string($itemArray['value']) && !is_numeric($itemArray['value'])) {
                try {
                    $result = $translator->translateText($itemArray['value'], $locale, 'en', true);
                    if ($result !== null) {
                        $translatedItem['value'] = $result;
                        $arrayChanged = true;
                    }
                    usleep(50000);
                } catch (\Exception $e) {
                    Log::error("TranslatePageJob: array object 'value' on {$modelClass}#{$record->_id}: {$e->getMessage()}");
                }
            }

            $translatedArray[] = (object) $translatedItem;
        }

        if ($arrayChanged) {
            $existing[$field] = $translatedArray;
            $needsSave = true;
        }
        continue;
    }

    // ── Simple array fields (e.g. unit_names: ["kg", "lb"]) ──
    if (is_array($raw)) {
        $translatedArray = [];
        $arrayChanged    = false;

        foreach ($raw as $item) {
            if (!is_string($item) || empty(trim($item))) {
                $translatedArray[] = $item;
                continue;
            }
            try {
                $result = $translator->translateText($item, $locale, 'en', true);
                $translatedArray[] = $result ?? $item;
                if ($result !== null) $arrayChanged = true;
                usleep(50000);
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

    Log::debug("TranslatePageJob: translating string field '{$field}' on {$modelClass}#{$record->_id}");

    try {
        $translated = $translator->translateText($original, $locale, 'en', true);

        if ($translated !== null) {
            $existing[$field] = $translated;
            $needsSave = true;
        }

        usleep(50000);

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
    protected function resolveModel(string $page): ?string
    {
        return self::PAGE_MODELS[$page] ?? null;
    }

    /**
     * @return list<string>
     */
    public static function supportedPages(): array
    {
        return array_keys(self::PAGE_MODELS);
    }
}

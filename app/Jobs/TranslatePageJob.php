<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\TranslationService;
use App\Services\TranslationNotifier;
use RuntimeException;
use Throwable;

class TranslatePageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800; // 30 minutes
    public int $tries   = 3;

    /**
     * Human-readable keys used inside PageSection.extra.
     *
     * The allow-list deliberately excludes URLs, icons, images, email
     * addresses, phone numbers, prices, and company names.
     *
     * @var list<string>
     */
    private const STRUCTURED_TEXT_KEYS = [
        'content',
        'title',
        'subtitle',
        'description',
        'desc',
        'button_text',
        'alt_tag',
        'label',
        'value',
        'question',
        'answer',
        'address',
        'phone_label',
        'email_label',
        'currency_note',
        'vehicle',
        'columns',
    ];

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
        public string $page,
        public ?string $requestedBy = null,
        public ?string $languageName = null,
        public ?string $runId = null,
    ) {}

    /**
     * @return array{total: int, processed: int, updated: int, failed: int}
     */
    public function handle(
        TranslationService $translator,
        ?TranslationNotifier $notifier = null,
    ): array
    {
        $modelClass = $this->resolveModel($this->page);

        if (!$modelClass || !class_exists($modelClass)) {
            throw new RuntimeException("No model is mapped for collection '{$this->page}'.");
        }

        $model    = new $modelClass;
        $fields   = $model->translatable ?? [];
        $locale   = $this->language;

        if (empty($fields)) {
            throw new RuntimeException("No translatable fields are configured for {$modelClass}.");
        }

        // Stream records in chunks to avoid memory issues
        $totalCount = $modelClass::count();
        $processed = 0;
        $updated = 0;
        $failed = 0;

        $modelClass::chunk(50, function ($records) use (
            $translator,
            $fields,
            $locale,
            $modelClass,
            $totalCount,
            &$processed,
            &$updated,
            &$failed,
        ) {
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

    // Page sections keep most visible copy in a nested `extra` document.
    // Translate only known human-readable keys and preserve its exact shape.
    if ($field === 'extra' && is_array($raw)) {
        [$translatedExtra, $extraChanged] = $this->translateStructuredExtra(
            $raw,
            $translator,
            $locale,
        );

        if ($extraChanged) {
            $existing[$field] = $translatedExtra;
            $needsSave = true;
        }
        continue;
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
                        $updated++;

                    } catch (\Exception $e) {
                        Log::error("TranslatePageJob: save failed for {$modelClass}#{$record->_id}: {$e->getMessage()}");
                        $failed++;
                    }
                }
            }
        });

        Log::info("TranslatePageJob: finished page='{$this->page}' lang='{$locale}'");

        $stats = [
            'total' => $totalCount,
            'processed' => $processed,
            'updated' => $updated,
            'failed' => $failed,
        ];

        if ($notifier && $this->requestedBy && $this->runId) {
            $notifier->completed(
                $this->requestedBy,
                $this->runId,
                $this->languageName ?: strtoupper($locale),
                $locale,
                $this->page,
                $stats,
            );
        }

        return $stats;
    }

    public function failed(Throwable $exception): void
    {
        if (!$this->requestedBy || !$this->runId) {
            return;
        }

        app(TranslationNotifier::class)->failed(
            $this->requestedBy,
            $this->runId,
            $this->languageName ?: strtoupper($this->language),
            $this->language,
            $this->page,
            $exception->getMessage(),
        );
    }

    /**
     * Recursively translate static-page content without modifying structural
     * data such as icons, links, prices, IDs, email addresses, or phone
     * numbers.
     *
     * @return array{0: mixed, 1: bool}
     */
    private function translateStructuredExtra(
        mixed $value,
        TranslationService $translator,
        string $locale,
        ?string $key = null,
    ): array {
        if (is_object($value) && method_exists($value, 'getArrayCopy')) {
            $value = $value->getArrayCopy();
        } elseif (is_object($value)) {
            $value = (array) $value;
        }

        if (is_string($value)) {
            if (!in_array($key, self::STRUCTURED_TEXT_KEYS, true) || trim(strip_tags($value)) === '') {
                return [$value, false];
            }

            try {
                $translated = $translator->translateText($value, $locale, 'en', true);
                usleep(50000);

                return $translated !== null
                    ? [$translated, true]
                    : [$value, false];
            } catch (\Exception $e) {
                Log::error("TranslatePageJob: structured field '{$key}': {$e->getMessage()}");
                return [$value, false];
            }
        }

        if (!is_array($value)) {
            return [$value, false];
        }

        $translated = [];
        $changed = false;

        foreach ($value as $childKey => $childValue) {
            $effectiveKey = is_string($childKey) ? $childKey : $key;
            [$translatedChild, $childChanged] = $this->translateStructuredExtra(
                $childValue,
                $translator,
                $locale,
                $effectiveKey,
            );

            $translated[$childKey] = $translatedChild;
            $changed = $changed || $childChanged;
        }

        return [$translated, $changed];
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

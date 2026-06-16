<?php

namespace App\Services;

use Aws\Translate\TranslateClient;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    private TranslateClient $client;

    public static array $modelMap = [
    'Blog'           => [\App\Models\Blog::class,           ['heading', 'description', 'blog_comments']],
    'Event'          => [\App\Models\Event::class,          ['heading', 'description', 'place']], // ← ADD
    'Product'        => [\App\Models\Product::class,        ['product_name', 'description']],
    'Brand'          => [\App\Models\Brand::class,          ['name']],
    'news'          => [\App\Models\News::class,           ['heading', 'content']],
    'PricePromotion' => [\App\Models\PricePromotion::class, ['heading', 'description']],
    'PageSection'    => [\App\Models\PageSection::class,    ['title', 'subtitle', 'description', 'button_text', 'extra']],
    'PvSpotPrice'    => [\App\Models\PvSpotPrice::class,    ['heading']],
    'main-menu'     => [\App\Models\MainMenu::class,      ['name', 'short_description', 'content']],
    'sub-menu'      => [\App\Models\SubMenu::class,       ['name', 'short_description', 'content']],
    'slider'        => [\App\Models\Slider::class,        ['name', 'alt_tag']],
    'brand'         => [\App\Models\Brand::class,         ['name']],
    'category'      => [\App\Models\Category::class,      ['name', 'short_description', 'content']],
    'sub-category'  => [\App\Models\SubCategory::class,  ['name', 'short_description', 'content']],
    'advertisement' => [\App\Models\Advertisement::class, ['heading', 'description']],
    'unit'          => [\App\Models\Unit::class,          ['name']],
    'country'       => [\App\Models\Country::class,       ['name']],
    'coupon'        => [\App\Models\Coupon::class,        ['code', 'description']],
    'incoterm'      => [\App\Models\Incoterm::class,      ['name', 'description']],
    'charge'        => [\App\Models\Charge::class,        ['name', 'description']],
    'product-specification' => [\App\Models\DetailOption::class, ['name', 'description']],
    'product-listing' => [\App\Models\ProductListing::class, ['incoterm']],
    'user'          => [\App\Models\User::class,          ['name']],
    'role'          => [\App\Models\Role::class,          ['name']],
    'sub-admin'     => [\App\Models\SubAdmin::class,     ['name']],
    'warehouse'     => [\App\Models\Warehouse::class,     ['name', 'location']],
    'offer'         => [\App\Models\Offer::class,         ['name', 'description']],
    'specification' => [\App\Models\DetailOption::class, ['name', 'description']],
    'static-page'   => [\App\Models\PageSection::class, ['title', 'subtitle', 'description', 'button_text', 'extra']],
    'commission'    => [\App\Models\Commission::class,    ['name', 'description']],
    'inventory'     => [\App\Models\Inventory::class,     ['name', 'description']],
    'inventory-transaction' => [\App\Models\InventoryTransaction::class, ['notes']],
    'location'      => [\App\Models\Location::class,      ['name', 'description']],
    'product-detail-option' => [\App\Models\ProductDetailOption::class, ['option_name', 'category_name', 'sub_category_name', 'unit_names']],

];

    public static array $languages = [
        'ar' => ['name' => 'Arabic',     'native' => 'العربية',   'flag' => '🇸🇦', 'rtl' => true],
        'fr' => ['name' => 'French',     'native' => 'Français',  'flag' => '🇫🇷', 'rtl' => false],
        'es' => ['name' => 'Spanish',    'native' => 'Español',   'flag' => '🇪🇸', 'rtl' => false],
        'de' => ['name' => 'German',     'native' => 'Deutsch',   'flag' => '🇩🇪', 'rtl' => false],
        'zh' => ['name' => 'Chinese',    'native' => '中文',       'flag' => '🇨🇳', 'rtl' => false],
        'ur' => ['name' => 'Urdu',       'native' => 'اردو',      'flag' => '🇵🇰', 'rtl' => true],
        'hi' => ['name' => 'Hindi',      'native' => 'हिन्दी',    'flag' => '🇮🇳', 'rtl' => false],
        'tr' => ['name' => 'Turkish',    'native' => 'Türkçe',    'flag' => '🇹🇷', 'rtl' => false],
        'ru' => ['name' => 'Russian',    'native' => 'Русский',   'flag' => '🇷🇺', 'rtl' => false],
        'it' => ['name' => 'Italian',    'native' => 'Italiano',  'flag' => '🇮🇹', 'rtl' => false],
        'pt' => ['name' => 'Portuguese', 'native' => 'Português', 'flag' => '🇵🇹', 'rtl' => false],
        'ja' => ['name' => 'Japanese',   'native' => '日本語',     'flag' => '🇯🇵', 'rtl' => false],
        'ko' => ['name' => 'Korean',     'native' => '한국어',     'flag' => '🇰🇷', 'rtl' => false],
    ];

    public static function getLanguages(): array
{
    $available = config('languages.available', []);
    $rtlCodes  = config('languages.rtl', []);

    unset($available['en']);

    $result = [];
    foreach ($available as $code => $name) {
        $result[$code] = [
            'name'   => self::$languages[$code]['name']   ?? $name,
            'native' => self::$languages[$code]['native'] ?? $name,
            'flag'   => self::$languages[$code]['flag']   ?? strtoupper($code),
            'rtl'    => in_array($code, $rtlCodes),
        ];
    }
    return $result;
}

    public function __construct()
    {
        $this->client = new TranslateClient([
            'version'     => 'latest',
            'region'      => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // PUBLIC: translate a single string (plain or HTML)
    // ─────────────────────────────────────────────────────────────
    public function translateText(string $text, string $targetLang, string $sourceLang = 'en'): ?string
    {
        $plainText = trim(strip_tags($text));
        if (empty($plainText))           return $text;
        if ($targetLang === $sourceLang) return $text;

        $isHtml   = ($text !== strip_tags($text));
        $cacheKey = 'trans_' . md5($text . '|' . $targetLang . '|' . $sourceLang . ($isHtml ? 'h' : 't'));

        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        try {
            $translated = $isHtml
                ? $this->translateHtmlFragment($text, $sourceLang, $targetLang)
                : $this->translatePlain($text, $sourceLang, $targetLang);

            if ($translated !== null) {
                Cache::put($cacheKey, $translated, now()->addDays(30));
            }
            return $translated;

        } catch (\Exception $e) {
            Log::error('TranslationService::translateText — ' . $e->getMessage());
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE: translate plain text via AWS translateText
    // ─────────────────────────────────────────────────────────────
    private function translatePlain(string $text, string $source, string $target): ?string
    {
        // AWS has a 10,000 byte limit — split if needed
        if (strlen($text) > 9000) {
            return $this->translateLongText($text, $source, $target);
        }

        try {
            $result = $this->client->translateText([
                'SourceLanguageCode' => $source,
                'TargetLanguageCode' => $target,
                'Text'               => $text,
            ]);

            // Throttle real API calls to stay under AWS Translate rate limits.
            usleep(150000); // 150ms

            return $result['TranslatedText'];
        } catch (AwsException $e) {
            Log::error('AWS translateText: ' . $e->getAwsErrorMessage() . ' [' . $e->getAwsErrorCode() . ']');
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE: translate an HTML fragment by walking text nodes
    //
    // WHY NOT translateDocument?
    //   translateDocument needs a FULL HTML document with <html><body>.
    //   Quill saves fragments like <p><strong>Hello</strong></p>.
    //   Passing a fragment to translateDocument fails silently —
    //   it returns the input unchanged (or throws a content-type error).
    //
    // THIS APPROACH:
    //   1. Wrap fragment in a div inside a minimal HTML document
    //   2. Walk every TEXT NODE (not the tags) and translate each one
    //   3. Re-assemble — tags are preserved perfectly
    // ─────────────────────────────────────────────────────────────
    private function translateHtmlFragment(string $html, string $source, string $target): ?string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);

        // Wrap in minimal document so DOMDocument parses reliably
        $wrapped = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'
                 . '<div id="xlat">' . $html . '</div>'
                 . '</body></html>';

        $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $wrapper = $dom->getElementById('xlat');
        if (!$wrapper) return $html; // parse failed — return original

        // Collect all leaf text nodes
        $textNodes = [];
        $this->collectTextNodes($wrapper, $textNodes);

        if (empty($textNodes)) return $html;

        // Translate each text node individually
        foreach ($textNodes as $node) {
            $original = $node->nodeValue;
            if (empty(trim($original))) continue;

            $translated = $this->translatePlain($original, $source, $target);
            if ($translated !== null) {
                $node->nodeValue = $translated;
            }
        }

        // Re-assemble just the inner HTML of our wrapper
        $result = '';
        foreach ($wrapper->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return $result ?: $html;
    }

    // Walk DOM tree collecting leaf XML_TEXT_NODEs
    private function collectTextNodes(\DOMNode $node, array &$out): void
    {
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                if (!empty(trim($child->nodeValue))) {
                    $out[] = $child;
                }
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                $this->collectTextNodes($child, $out);
            }
        }
    }

    // Split very long plain text into chunks and translate each
    private function translateLongText(string $text, string $source, string $target): ?string
    {
        $sentences  = preg_split('/(?<=[.!?])\s+/', $text);
        $chunk      = '';
        $translated = '';

        foreach ($sentences as $sentence) {
            if (strlen($chunk) + strlen($sentence) > 8000) {
                $t = $this->translatePlain($chunk, $source, $target);
                $translated .= ($t ?? $chunk) . ' ';
                $chunk = '';
            }
            $chunk .= $sentence . ' ';
        }

        if (!empty(trim($chunk))) {
            $t = $this->translatePlain(trim($chunk), $source, $target);
            $translated .= $t ?? $chunk;
        }

        return trim($translated) ?: null;
    }

    // ─────────────────────────────────────────────────────────────
    // Keys inside `extra` that hold non-text data (paths, links,
    // contacts, numbers, emojis) — never sent to AWS, copied as-is.
    // ─────────────────────────────────────────────────────────────
    private array $nonTranslatableKeys = [
        'logo', 'logo_file', 'image', 'icon', 'link', 'button_link',
        'url', 'href', 'email', 'phone', 'address_link', 'prices',
        'price', 'order', 'id', 'color', 'colors',
    ];

    private function isNonTranslatableKey($key): bool
    {
        if (!is_string($key)) return false;
        $key = strtolower($key);
        if (in_array($key, $this->nonTranslatableKeys, true)) return true;
        // Prefix/suffix conventions: social_facebook, *_link, *_url, *_file
        if (str_starts_with($key, 'social_')) return true;
        foreach (['_link', '_url', '_file', '_id'] as $suffix) {
            if (str_ends_with($key, $suffix)) return true;
        }
        return false;
    }

    // Decide whether a raw string value is worth translating
    private function isTranslatableString(string $value): bool
    {
        $plain = trim(strip_tags($value));
        if ($plain === '')                       return false;   // empty / tags only
        if (is_numeric($plain))                  return false;   // "100", "12.5"
        if (preg_match('#^https?://#i', $plain)) return false;   // URLs
        if (filter_var($plain, FILTER_VALIDATE_EMAIL)) return false; // emails
        // File paths like "logos/home/abc.png"
        if (preg_match('#\.(png|jpe?g|gif|svg|webp|pdf)$#i', $plain)) return false;
        // Must contain at least one letter to be translatable
        return (bool) preg_match('/\p{L}/u', $plain);
    }

    // ─────────────────────────────────────────────────────────────
    // Recursively translate every translatable string inside an
    // array (e.g. PageSection.extra), preserving structure & keys.
    // ─────────────────────────────────────────────────────────────
    private function translateArrayRecursive($value, string $targetLang)
    {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                if ($this->isNonTranslatableKey($k)) {
                    $out[$k] = $v;          // keep paths/links/contacts untouched
                    continue;
                }
                $out[$k] = $this->translateArrayRecursive($v, $targetLang);
            }
            return $out;
        }

        if (is_string($value)) {
            if (!$this->isTranslatableString($value)) return $value;
            $translated = $this->translateText($value, $targetLang);
            return $translated ?? $value;
        }

        // numbers, bools, null — leave as-is
        return $value;
    }

    // True if there is at least one translatable string anywhere inside
    private function arrayHasTranslatableText($value): bool
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if ($this->isNonTranslatableKey($k)) continue;
                if ($this->arrayHasTranslatableText($v)) return true;
            }
            return false;
        }
        return is_string($value) && $this->isTranslatableString($value);
    }

    // ─────────────────────────────────────────────────────────────
    // Translate one model record and SAVE into the record itself
    // record.ar = { heading: "...", description: "<p>...</p>", blog_comments: "..." }
    // ─────────────────────────────────────────────────────────────
    public function translateAndSave($record, array $fields, string $targetLang): array
    {
        $existing = $record->$targetLang ?? [];
        if (!is_array($existing)) $existing = [];

        // Source fingerprints from the last translation run — used to detect
        // edits to the English source so we re-translate only what changed.
        $fingerprints = (isset($existing['_src']) && is_array($existing['_src']))
            ? $existing['_src'] : [];

        $newTranslations = [];
        $newFingerprints = $fingerprints;   // carry forward, overwrite per field

        foreach ($fields as $field) {
            $value = $record->$field ?? null;

            // Is there anything translatable in this field?
            $hasContent = is_array($value)
                ? $this->arrayHasTranslatableText($value)
                : !empty(trim(strip_tags((string) ($value ?? ''))));
            if (!$hasContent) continue;

            // Skip when already translated AND the source hasn't changed.
            $fp = $this->fieldFingerprint($value);
            if (!empty($existing[$field]) && ($fingerprints[$field] ?? null) === $fp) {
                continue;
            }

            // ── Array field (e.g. PageSection.extra) → translate recursively ──
            if (is_array($value)) {
                $newTranslations[$field] = $this->translateArrayRecursive($value, $targetLang);
                $newFingerprints[$field] = $fp;
                continue;
            }

            // ── Plain string field ──
            $translated = $this->translateText((string) $value, $targetLang);

            if ($translated !== null) {
                $newTranslations[$field] = $translated;
                $newFingerprints[$field] = $fp;
            } else {
                Log::warning("TranslationService: null result — model=" . get_class($record)
                    . " id={$record->id} field={$field} lang={$targetLang}");
            }
        }

        if (!empty($newTranslations)) {
            $merged = array_merge($existing, $newTranslations);
            $merged['_src'] = $newFingerprints;
            $record->setAttribute($targetLang, $merged);
            $record->save();
        }

        return [
            'success'    => true,
            'translated' => count($newTranslations),
            'skipped'    => count($fields) - count($newTranslations),
        ];
    }

    // Fingerprint of a source field value — changes whenever the admin
    // edits the English content, triggering a re-translation on next run.
    private function fieldFingerprint($value): string
    {
        return md5(is_array($value) ? json_encode($value) : (string) ($value ?? ''));
    }

    // ─────────────────────────────────────────────────────────────
    // Translate ALL records for one model key
    // ─────────────────────────────────────────────────────────────
    public function translateAllRecords(string $modelKey, string $targetLang): array
    {
        if (!isset(self::$modelMap[$modelKey])) {
            return ['success' => false, 'error' => "Unknown model: $modelKey"];
        }

        [$modelClass, $fields] = self::$modelMap[$modelKey];

        if (!class_exists($modelClass)) {
            return ['success' => false, 'error' => "Class $modelClass not found"];
        }

        $records    = $modelClass::all();
        $translated = 0;
        $skipped    = 0;
        $failed     = 0;

        foreach ($records as $record) {
            // Skip only when every content field is already translated AND
            // its source is unchanged since the last run (fingerprint match).
            $existing = $record->$targetLang ?? [];
            if (is_array($existing) && !empty($existing)) {
                $fingerprints = (isset($existing['_src']) && is_array($existing['_src']))
                    ? $existing['_src'] : [];
                $needsWork = false;
                foreach ($fields as $field) {
                    $value = $record->$field ?? null;
                    $hasContent = is_array($value)
                        ? $this->arrayHasTranslatableText($value)
                        : !empty(trim(strip_tags((string) ($value ?? ''))));
                    if (!$hasContent) continue;

                    $hasTranslation = !empty($existing[$field]);
                    $sourceChanged  = ($fingerprints[$field] ?? null) !== $this->fieldFingerprint($value);
                    if (!$hasTranslation || $sourceChanged) { $needsWork = true; break; }
                }
                if (!$needsWork) { $skipped++; continue; }
            }

            try {
                $result = $this->translateAndSave($record, $fields, $targetLang);
                if ($result['translated'] > 0) $translated++;
                else                           $skipped++;
            } catch (\Exception $e) {
                Log::error("translateAllRecords {$modelKey}#{$record->id}: " . $e->getMessage());
                $failed++;
            }
        }

        return [
            'success'    => true,
            'model'      => $modelKey,
            'total'      => $records->count(),
            'translated' => $translated,
            'skipped'    => $skipped,
            'failed'     => $failed,
        ];
    }
}
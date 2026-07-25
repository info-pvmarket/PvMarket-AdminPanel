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
        'PageSection'    => [\App\Models\PageSection::class,    ['title', 'subtitle', 'description', 'button_text', 'alt_tag', 'extra']],
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
        'static-page'   => [\App\Models\PageSection::class, ['title', 'subtitle', 'description', 'button_text', 'alt_tag', 'extra']],
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

    // public static array $languages = [
    //     'af'    => ['name' => 'Afrikaans',              'native' => 'Afrikaans',           'flag' => '🇿🇦', 'rtl' => false],
    //     'sq'    => ['name' => 'Albanian',               'native' => 'Shqip',               'flag' => '🇦🇱', 'rtl' => false],
    //     'am'    => ['name' => 'Amharic',                'native' => 'አማርኛ',              'flag' => '🇪🇹', 'rtl' => false],
    //     'ar'    => ['name' => 'Arabic',                 'native' => 'العربية',             'flag' => '🇸🇦', 'rtl' => true],
    //     'hy'    => ['name' => 'Armenian',               'native' => 'Հայերեն',            'flag' => '🇦🇲', 'rtl' => false],
    //     'az'    => ['name' => 'Azerbaijani',            'native' => 'Azərbaycanca',        'flag' => '🇦🇿', 'rtl' => false],
    //     'bn'    => ['name' => 'Bengali',                'native' => 'বাংলা',              'flag' => '🇧🇩', 'rtl' => false],
    //     'bs'    => ['name' => 'Bosnian',                'native' => 'Bosanski',            'flag' => '🇧🇦', 'rtl' => false],
    //     'bg'    => ['name' => 'Bulgarian',              'native' => 'Български',          'flag' => '🇧🇬', 'rtl' => false],
    //     'ca'    => ['name' => 'Catalan',                'native' => 'Català',              'flag' => '🇪🇸', 'rtl' => false],
    //     'zh'    => ['name' => 'Chinese (Simplified)',   'native' => '简体中文',            'flag' => '🇨🇳', 'rtl' => false],
    //     'zh-TW' => ['name' => 'Chinese (Traditional)',  'native' => '繁體中文',            'flag' => '🇹🇼', 'rtl' => false],
    //     'hr'    => ['name' => 'Croatian',               'native' => 'Hrvatski',            'flag' => '🇭🇷', 'rtl' => false],
    //     'cs'    => ['name' => 'Czech',                  'native' => 'Čeština',             'flag' => '🇨🇿', 'rtl' => false],
    //     'da'    => ['name' => 'Danish',                 'native' => 'Dansk',               'flag' => '🇩🇰', 'rtl' => false],
    //     'fa-AF' => ['name' => 'Dari',                   'native' => 'دری',                 'flag' => '🇦🇫', 'rtl' => true],
    //     'nl'    => ['name' => 'Dutch',                  'native' => 'Nederlands',          'flag' => '🇳🇱', 'rtl' => false],
    //     'en'    => ['name' => 'English',                'native' => 'English',             'flag' => '🇺🇸', 'rtl' => false],
    //     'et'    => ['name' => 'Estonian',               'native' => 'Eesti',               'flag' => '🇪🇪', 'rtl' => false],
    //     'fa'    => ['name' => 'Persian',                'native' => 'فارسی',              'flag' => '🇮🇷', 'rtl' => true],
    //     'tl'    => ['name' => 'Filipino',               'native' => 'Filipino',            'flag' => '🇵🇭', 'rtl' => false],
    //     'fi'    => ['name' => 'Finnish',                'native' => 'Suomi',               'flag' => '🇫🇮', 'rtl' => false],
    //     'fr'    => ['name' => 'French',                 'native' => 'Français',            'flag' => '🇫🇷', 'rtl' => false],
    //     'fr-CA' => ['name' => 'French (Canada)',        'native' => 'Français (Canada)',   'flag' => '🇨🇦', 'rtl' => false],
    //     'ka'    => ['name' => 'Georgian',               'native' => 'ქართული',            'flag' => '🇬🇪', 'rtl' => false],
    //     'de'    => ['name' => 'German',                 'native' => 'Deutsch',             'flag' => '🇩🇪', 'rtl' => false],
    //     'el'    => ['name' => 'Greek',                  'native' => 'Ελληνικά',           'flag' => '🇬🇷', 'rtl' => false],
    //     'gu'    => ['name' => 'Gujarati',               'native' => 'ગુજરાતી',            'flag' => '🇮🇳', 'rtl' => false],
    //     'ht'    => ['name' => 'Haitian Creole',         'native' => 'Kreyòl Ayisyen',      'flag' => '🇭🇹', 'rtl' => false],
    //     'ha'    => ['name' => 'Hausa',                  'native' => 'Hausa',               'flag' => '🇳🇬', 'rtl' => false],
    //     'he'    => ['name' => 'Hebrew',                 'native' => 'עברית',              'flag' => '🇮🇱', 'rtl' => true],
    //     'hi'    => ['name' => 'Hindi',                  'native' => 'हिन्दी',             'flag' => '🇮🇳', 'rtl' => false],
    //     'hu'    => ['name' => 'Hungarian',              'native' => 'Magyar',              'flag' => '🇭🇺', 'rtl' => false],
    //     'is'    => ['name' => 'Icelandic',              'native' => 'Íslenska',            'flag' => '🇮🇸', 'rtl' => false],
    //     'id'    => ['name' => 'Indonesian',             'native' => 'Bahasa Indonesia',    'flag' => '🇮🇩', 'rtl' => false],
    //     'ga'    => ['name' => 'Irish',                  'native' => 'Gaeilge',             'flag' => '🇮🇪', 'rtl' => false],
    //     'it'    => ['name' => 'Italian',                'native' => 'Italiano',            'flag' => '🇮🇹', 'rtl' => false],
    //     'ja'    => ['name' => 'Japanese',               'native' => '日本語',              'flag' => '🇯🇵', 'rtl' => false],
    //     'kn'    => ['name' => 'Kannada',                'native' => 'ಕನ್ನಡ',              'flag' => '🇮🇳', 'rtl' => false],
    //     'kk'    => ['name' => 'Kazakh',                 'native' => 'Қазақша',            'flag' => '🇰🇿', 'rtl' => false],
    //     'ko'    => ['name' => 'Korean',                 'native' => '한국어',              'flag' => '🇰🇷', 'rtl' => false],
    //     'lv'    => ['name' => 'Latvian',                'native' => 'Latviešu',            'flag' => '🇱🇻', 'rtl' => false],
    //     'lt'    => ['name' => 'Lithuanian',             'native' => 'Lietuvių',            'flag' => '🇱🇹', 'rtl' => false],
    //     'mk'    => ['name' => 'Macedonian',             'native' => 'Македонски',         'flag' => '🇲🇰', 'rtl' => false],
    //     'ms'    => ['name' => 'Malay',                  'native' => 'Bahasa Melayu',       'flag' => '🇲🇾', 'rtl' => false],
    //     'ml'    => ['name' => 'Malayalam',              'native' => 'മലയാളം',            'flag' => '🇮🇳', 'rtl' => false],
    //     'mt'    => ['name' => 'Maltese',                'native' => 'Malti',               'flag' => '🇲🇹', 'rtl' => false],
    //     'mr'    => ['name' => 'Marathi',                'native' => 'मराठी',              'flag' => '🇮🇳', 'rtl' => false],
    //     'mn'    => ['name' => 'Mongolian',              'native' => 'Монгол',             'flag' => '🇲🇳', 'rtl' => false],
    //     'no'    => ['name' => 'Norwegian',              'native' => 'Norsk',               'flag' => '🇳🇴', 'rtl' => false],
    //     'ps'    => ['name' => 'Pashto',                 'native' => 'پښتو',               'flag' => '🇦🇫', 'rtl' => true],
    //     'pl'    => ['name' => 'Polish',                 'native' => 'Polski',              'flag' => '🇵🇱', 'rtl' => false],
    //     'pt'    => ['name' => 'Portuguese (Brazil)',    'native' => 'Português',           'flag' => '🇧🇷', 'rtl' => false],
    //     'pt-PT' => ['name' => 'Portuguese (Portugal)',  'native' => 'Português',           'flag' => '🇵🇹', 'rtl' => false],
    //     'pa'    => ['name' => 'Punjabi',                'native' => 'ਪੰਜਾਬੀ',             'flag' => '🇮🇳', 'rtl' => false],
    //     'ro'    => ['name' => 'Romanian',               'native' => 'Română',              'flag' => '🇷🇴', 'rtl' => false],
    //     'ru'    => ['name' => 'Russian',                'native' => 'Русский',            'flag' => '🇷🇺', 'rtl' => false],
    //     'sr'    => ['name' => 'Serbian',                'native' => 'Српски',             'flag' => '🇷🇸', 'rtl' => false],
    //     'si'    => ['name' => 'Sinhala',                'native' => 'සිංහල',             'flag' => '🇱🇰', 'rtl' => false],
    //     'sk'    => ['name' => 'Slovak',                 'native' => 'Slovenčina',          'flag' => '🇸🇰', 'rtl' => false],
    //     'sl'    => ['name' => 'Slovenian',              'native' => 'Slovenščina',         'flag' => '🇸🇮', 'rtl' => false],
    //     'so'    => ['name' => 'Somali',                 'native' => 'Soomaali',            'flag' => '🇸🇴', 'rtl' => false],
    //     'es'    => ['name' => 'Spanish',                'native' => 'Español',             'flag' => '🇪🇸', 'rtl' => false],
    //     'es-MX' => ['name' => 'Spanish (Mexico)',       'native' => 'Español (México)',    'flag' => '🇲🇽', 'rtl' => false],
    //     'sw'    => ['name' => 'Swahili',                'native' => 'Kiswahili',           'flag' => '🇹🇿', 'rtl' => false],
    //     'sv'    => ['name' => 'Swedish',                'native' => 'Svenska',             'flag' => '🇸🇪', 'rtl' => false],
    //     'ta'    => ['name' => 'Tamil',                  'native' => 'தமிழ்',              'flag' => '🇮🇳', 'rtl' => false],
    //     'te'    => ['name' => 'Telugu',                 'native' => 'తెలుగు',             'flag' => '🇮🇳', 'rtl' => false],
    //     'th'    => ['name' => 'Thai',                   'native' => 'ไทย',                'flag' => '🇹🇭', 'rtl' => false],
    //     'tr'    => ['name' => 'Turkish',                'native' => 'Türkçe',              'flag' => '🇹🇷', 'rtl' => false],
    //     'uk'    => ['name' => 'Ukrainian',              'native' => 'Українська',         'flag' => '🇺🇦', 'rtl' => false],
    //     'ur'    => ['name' => 'Urdu',                   'native' => 'اردو',                'flag' => '🇵🇰', 'rtl' => true],
    //     'uz'    => ['name' => 'Uzbek',                  'native' => 'Oʻzbek',              'flag' => '🇺🇿', 'rtl' => false],
    //     'vi'    => ['name' => 'Vietnamese',             'native' => 'Tiếng Việt',          'flag' => '🇻🇳', 'rtl' => false],
    //     'cy'    => ['name' => 'Welsh',                  'native' => 'Cymraeg',             'flag' => '🏴', 'rtl' => false],
    // ];

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
    public function translateText(
        string $text,
        string $targetLang,
        string $sourceLang = 'en',
        bool $refreshCache = false,
    ): ?string
    {
        $plainText = trim(strip_tags($text));
        if (empty($plainText))           return $text;
        if ($targetLang === $sourceLang) return $text;

        $isHtml   = ($text !== strip_tags($text));
        $cacheKey = 'trans_' . md5($text . '|' . $targetLang . '|' . $sourceLang . ($isHtml ? 'h' : 't'));

        if (!$refreshCache) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) return $cached;
        }

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
    // Translate one model record and SAVE into the record itself
    // record.ar = { heading: "...", description: "<p>...</p>", blog_comments: "..." }
    // ─────────────────────────────────────────────────────────────
    public function translateAndSave($record, array $fields, string $targetLang): array
    {
        $existing = $record->$targetLang ?? [];
        if (!is_array($existing)) $existing = [];

        $newTranslations = [];

        foreach ($fields as $field) {
            $original = (string) ($record->$field ?? '');

            // Skip empty fields
            if (empty(trim(strip_tags($original)))) continue;

            // Skip already-translated fields
            if (!empty($existing[$field])) continue;

            $translated = $this->translateText($original, $targetLang);

            if ($translated !== null) {
                $newTranslations[$field] = $translated;
            } else {
                Log::warning("TranslationService: null result — model=" . get_class($record)
                    . " id={$record->id} field={$field} lang={$targetLang}");
            }
        }

        if (!empty($newTranslations)) {
            $merged = array_merge($existing, $newTranslations);
            $record->setAttribute($targetLang, $merged);
            $record->save();
        }

        return [
            'success'    => true,
            'translated' => count($newTranslations),
            'skipped'    => count($fields) - count($newTranslations),
        ];
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
            // Check if every content field is already translated
            $existing = $record->$targetLang ?? [];
            if (is_array($existing) && !empty($existing)) {
                $needsWork = false;
                foreach ($fields as $field) {
                    $hasContent     = !empty(trim(strip_tags((string) ($record->$field ?? ''))));
                    $hasTranslation = !empty($existing[$field]);
                    if ($hasContent && !$hasTranslation) {
                        $needsWork = true;
                        break;
                    }
                }
                if (!$needsWork) {
                    $skipped++;
                    continue;
                }
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

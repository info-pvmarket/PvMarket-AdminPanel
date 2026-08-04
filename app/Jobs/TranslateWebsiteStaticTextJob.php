<?php

namespace App\Jobs;

use App\Services\TranslationNotifier;
use App\Services\TranslationService;
use App\Services\WebsiteTranslationStore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class TranslateWebsiteStaticTextJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const COLLECTION = 'website-static-text';

    public int $timeout = 1800;

    public int $tries = 3;

    /**
     * Give AWS Translate time to recover when a complete catalog run is
     * throttled. Without queue backoff, an immediate retry repeats the same
     * burst and can fail every value again.
     *
     * @var list<int>
     */
    public array $backoff = [60, 180];

    private const TRANSLATION_ATTEMPTS = 3;

    private const RETRY_DELAYS_MICROSECONDS = [250000, 750000];

    public function __construct(
        public string $language,
        public ?string $requestedBy = null,
        public ?string $languageName = null,
        public ?string $runId = null,
    ) {}

    /**
     * Translate the frontend's canonical English catalog and replace the
     * matching nested language bundle in MongoDB.
     *
     * @return array{total: int, processed: int, updated: int, failed: int}
     */
    public function handle(
        TranslationService $translator,
        WebsiteTranslationStore $store,
        ?TranslationNotifier $notifier = null,
    ): array {
        $source = $this->fetchSourceCatalog();
        $stats = [
            'total' => $this->countStrings($source['sections']),
            'processed' => 0,
            'updated' => 0,
            'failed' => 0,
        ];

        $translatedSections = $this->translateValue(
            $source['sections'],
            $translator,
            $stats,
        );

        if ($stats['processed'] > 0 && $stats['updated'] === 0) {
            throw new RuntimeException(
                "Website static text translation failed for every text value ({$stats['failed']}/{$stats['processed']})."
            );
        }

        $sourceHash = hash(
            'sha256',
            json_encode(
                $source['sections'],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
            ),
        );

        $store->replace('en', [
            'version' => (int) ($source['version'] ?? 1),
            'language' => 'en',
            'source_language' => 'en',
            'source_hash' => $sourceHash,
            'content_hash' => $sourceHash,
            'generated_at' => now(),
            'sections' => $source['sections'],
        ]);

        $payload = [
            'version' => (int) ($source['version'] ?? 1),
            'language' => $this->language,
            'source_language' => 'en',
            'source_hash' => $sourceHash,
            'content_hash' => hash(
                'sha256',
                json_encode(
                    $translatedSections,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
                ),
            ),
            'generated_at' => now(),
            'sections' => $translatedSections,
        ];

        $store->replace($this->language, $payload);

        Log::info('TranslateWebsiteStaticTextJob: MongoDB bundle replaced', [
            'language' => $this->language,
            'stats' => $stats,
        ]);

        if ($notifier && $this->requestedBy && $this->runId) {
            $notifier->completed(
                $this->requestedBy,
                $this->runId,
                $this->languageName ?: strtoupper($this->language),
                $this->language,
                self::COLLECTION,
                $stats,
            );
        }

        return $stats;
    }

    public function failed(Throwable $exception): void
    {
        if (! $this->requestedBy || ! $this->runId) {
            return;
        }

        app(TranslationNotifier::class)->failed(
            $this->requestedBy,
            $this->runId,
            $this->languageName ?: strtoupper($this->language),
            $this->language,
            self::COLLECTION,
            $exception->getMessage(),
        );
    }

    /**
     * @return array{version?: int, language?: string, sections: array<string, mixed>}
     */
    private function fetchSourceCatalog(): array
    {
        $frontendUrl = rtrim((string) config('services.frontend.url'), '/');

        if ($frontendUrl === '') {
            throw new RuntimeException('The frontend URL is not configured.');
        }

        $response = Http::acceptJson()
            ->timeout(30)
            ->retry(3, 500)
            ->get($frontendUrl.'/api/website-translations/source');

        if (! $response->successful()) {
            throw new RuntimeException(
                "The website translation source returned HTTP {$response->status()}."
            );
        }

        $source = $response->json();

        if (
            ! is_array($source)
            || ($source['language'] ?? null) !== 'en'
            || ! isset($source['sections'])
            || ! is_array($source['sections'])
            || $source['sections'] === []
        ) {
            throw new RuntimeException('The website translation source is invalid or empty.');
        }

        return $source;
    }

    /**
     * @param  array{total: int, processed: int, updated: int, failed: int}  $stats
     */
    private function translateValue(
        mixed $value,
        TranslationService $translator,
        array &$stats,
    ): mixed {
        if (is_string($value)) {
            if (trim($value) === '') {
                return $value;
            }

            $stats['processed']++;
            [$protected, $placeholders] = $this->protectPlaceholders($value);
            $translated = $this->translateWithRetry($protected, $translator);

            if ($translated === null) {
                $stats['failed']++;

                return $value;
            }

            $stats['updated']++;
            usleep(50000);

            return strtr($translated, $placeholders);
        }

        if (! is_array($value)) {
            return $value;
        }

        $translated = [];
        foreach ($value as $key => $child) {
            $translated[$key] = $this->translateValue($child, $translator, $stats);
        }

        return $translated;
    }

    private function translateWithRetry(
        string $text,
        TranslationService $translator,
    ): ?string {
        for ($attempt = 1; $attempt <= self::TRANSLATION_ATTEMPTS; $attempt++) {
            $translated = $translator->translateText(
                $text,
                $this->language,
                'en',
                true,
            );

            if ($translated !== null) {
                return $translated;
            }

            if ($attempt < self::TRANSLATION_ATTEMPTS) {
                Log::warning('TranslateWebsiteStaticTextJob: retrying failed text value', [
                    'language' => $this->language,
                    'attempt' => $attempt + 1,
                ]);
                usleep(self::RETRY_DELAYS_MICROSECONDS[$attempt - 1]);
            }
        }

        return null;
    }

    /**
     * Preserve interpolation tokens such as {count} while AWS translates the
     * surrounding copy.
     *
     * @return array{0: string, 1: array<string, string>}
     */
    private function protectPlaceholders(string $value): array
    {
        $placeholders = [];
        $protected = preg_replace_callback(
            '/\{[A-Za-z0-9_.-]+\}/',
            function (array $match) use (&$placeholders): string {
                $token = '__PVMARKET_TOKEN_'.count($placeholders).'__';
                $placeholders[$token] = $match[0];

                return $token;
            },
            $value,
        );

        return [$protected ?? $value, $placeholders];
    }

    private function countStrings(mixed $value): int
    {
        if (is_string($value)) {
            return trim($value) === '' ? 0 : 1;
        }

        if (! is_array($value)) {
            return 0;
        }

        return array_sum(array_map(fn (mixed $child): int => $this->countStrings($child), $value));
    }
}

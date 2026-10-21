<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Moves base64 images pasted into rich text (blogs, news, events, page
 * sections) out of MongoDB into the public disk, replacing each data: URI
 * with the stored file's URL (TASK-6.2 / 6.3).
 *
 * Translated copies ({locale}.field) are migrated too, because
 * attachTranslations() copies the HTML - images included - into every
 * language. Files are named by content hash, so the copies share one file
 * and a re-run writes nothing new.
 */
class MigrateInlineImages extends Command
{
    protected $signature = 'content:migrate-inline-images
                            {--dry-run : Report what would change and write the mapping CSV, but change nothing}
                            {--force : Apply without the confirmation prompt}';

    protected $description = 'Move base64 images out of rich-text content into the public disk';

    /** Rich-text fields per collection, as the Go API reads them. */
    private const FIELDS = [
        'blogs'         => ['data', 'content'],
        'news'          => ['content', 'data'],
        'events'        => ['description'],
        'page_sections' => ['extra'],
    ];

    private const EXTENSIONS = ['png' => 'png', 'jpeg' => 'jpg', 'jpg' => 'jpg', 'gif' => 'gif', 'webp' => 'webp'];

    /** <img ... src="data:image/png;base64,...."> - possessive so long payloads do not backtrack. */
    private const DATA_IMAGE = '/(<img\b[^>]*?\bsrc\s*=\s*)(["\'])data:image\/([a-z0-9.+-]+);base64,([^"\']++)\2/i';

    /** @var resource */
    private $csv;

    /** Stored paths written (or planned) this run. */
    private array $stored = [];

    private array $totals = ['documents' => 0, 'images' => 0, 'files' => 0, 'bytes' => 0, 'skipped' => 0];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (! $dryRun && ! $this->option('force')
            && ! $this->confirm('Write the image files and update the content in MongoDB?')) {
            return self::FAILURE;
        }

        ini_set('pcre.backtrack_limit', '100000000');

        $csvPath = 'inline-image-migration/' . now()->format('Ymd-His') . ($dryRun ? '-dry-run' : '-apply') . '.csv';
        Storage::disk('local')->makeDirectory('inline-image-migration');
        $this->csv = fopen(Storage::disk('local')->path($csvPath), 'w');
        fputcsv($this->csv, ['collection', 'id', 'field', 'sha1', 'mime', 'bytes', 'path', 'url', 'status']);

        $locales  = array_values(array_diff(array_keys(config('languages.available', [])), ['en']));
        $database = DB::connection('mongodb')->getDatabase();

        foreach (self::FIELDS as $collectionName => $fields) {
            $collection = $database->selectCollection($collectionName);

            $paths = $fields;
            foreach ($locales as $locale) {
                foreach ($fields as $field) {
                    $paths[] = "{$locale}.{$field}";
                }
            }

            $cursor = $collection->find([], [
                'projection' => array_fill_keys(array_merge($fields, $locales), 1),
                'typeMap'    => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
            ]);

            foreach ($cursor as $document) {
                $id  = (string) $document['_id'];
                $set = [];

                foreach ($paths as $path) {
                    $value = data_get($document, $path);
                    if ($value === null) {
                        continue;
                    }

                    // page_sections.extra may be stored as a JSON string.
                    $isJson   = is_string($value) && $this->isJsonContainer($value);
                    $original = $isJson ? json_decode($value, true) : $value;
                    $migrated = $this->rewrite($original, $collectionName, $id, $path, $dryRun);

                    if ($migrated !== $original) {
                        $set[$path] = $isJson
                            ? json_encode($migrated, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                            : $migrated;
                    }
                }

                if ($set) {
                    $this->totals['documents']++;
                    if (! $dryRun) {
                        // A raw $set: no model events, no re-translation, no timestamps.
                        $collection->updateOne(['_id' => $document['_id']], ['$set' => $set]);
                    }
                }
            }
        }

        fclose($this->csv);

        $this->table(['Documents', 'Images', 'Files', 'Size', 'Skipped'], [[
            $this->totals['documents'],
            $this->totals['images'],
            $this->totals['files'],
            number_format($this->totals['bytes'] / 1048576, 2) . ' MB',
            $this->totals['skipped'],
        ]]);
        $this->info('Mapping CSV: ' . Storage::disk('local')->path($csvPath));
        $this->line($dryRun ? 'Dry run: nothing was written.' : 'Applied.');

        return self::SUCCESS;
    }

    /** Replace every data: image in $value (a string, or an array walked recursively). */
    private function rewrite(mixed $value, string $collection, string $id, string $path, bool $dryRun): mixed
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->rewrite($item, $collection, $id, "{$path}.{$key}", $dryRun);
            }
            return $value;
        }

        if (! is_string($value) || stripos($value, 'data:image/') === false) {
            return $value;
        }

        $result = preg_replace_callback(self::DATA_IMAGE, function (array $match) use ($collection, $id, $path, $dryRun) {
            $type  = strtolower($match[3]);
            $ext   = self::EXTENSIONS[$type] ?? null;
            $bytes = base64_decode(preg_replace('/\s+/', '', $match[4]), true);

            if (! $ext || $bytes === false || $bytes === '') {
                $this->totals['skipped']++;
                $this->row($collection, $id, $path, '', "image/{$type}", 0, '', '', 'skipped: unsupported type or invalid base64');
                return $match[0];
            }

            $sha1       = sha1($bytes);
            $storedPath = "inline-images/{$collection}/{$sha1}.{$ext}";
            $url        = Storage::disk('public')->url($storedPath);

            if (! isset($this->stored[$storedPath])) {
                $this->stored[$storedPath] = true;
                $this->totals['files']++;
                $this->totals['bytes'] += strlen($bytes);
                if (! $dryRun && ! Storage::disk('public')->exists($storedPath)) {
                    Storage::disk('public')->put($storedPath, $bytes, 'public');
                }
            }

            $this->totals['images']++;
            $this->row($collection, $id, $path, $sha1, "image/{$type}", strlen($bytes), $storedPath, $url, $dryRun ? 'would move' : 'moved');

            return $match[1] . $match[2] . $url . $match[2];
        }, $value);

        if ($result === null) {
            $this->warn("Could not parse {$collection} {$id} {$path}: left unchanged");
            return $value;
        }

        return $result;
    }

    private function isJsonContainer(string $value): bool
    {
        $trimmed = ltrim($value);
        return (str_starts_with($trimmed, '{') || str_starts_with($trimmed, '['))
            && is_array(json_decode($value, true));
    }

    private function row(string ...$columns): void
    {
        fputcsv($this->csv, $columns);
    }
}
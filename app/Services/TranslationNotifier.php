<?php

namespace App\Services;

use App\Jobs\TranslateWebsiteStaticTextJob;
use App\Models\Notification;

class TranslationNotifier
{
    /**
     * @param array{total: int, processed: int, updated: int, failed: int} $stats
     */
    public function completed(
        string $userId,
        string $runId,
        string $languageName,
        string $languageCode,
        string $collection,
        array $stats,
    ): void {
        $label = $this->collectionLabel($collection);
        $hasErrors = $stats['failed'] > 0;
        $isWebsiteText = $collection === TranslateWebsiteStaticTextJob::COLLECTION;
        $unit = $isWebsiteText ? 'text value' : 'record';
        $result = $isWebsiteText
            ? "replaced {$stats['updated']} text value(s)"
            : "updated {$stats['updated']} record(s)";

        Notification::create([
            'user_id' => $userId,
            'type' => $hasErrors ? 'translation_completed_with_errors' : 'translation_completed',
            'title' => $hasErrors
                ? "{$languageName} translation completed with errors"
                : "{$languageName} translation completed",
            'message' => sprintf(
                '%s: processed %d %s(s), %s%s.',
                $label,
                $stats['processed'],
                $unit,
                $result,
                $hasErrors ? ", and failed to translate {$stats['failed']} {$unit}(s)" : '',
            ),
            'metadata' => [
                'translation_run_id' => $runId,
                'language_code' => $languageCode,
                'collection' => $collection,
                'total_records' => $stats['total'],
                'processed_records' => $stats['processed'],
                'updated_records' => $stats['updated'],
                'failed_records' => $stats['failed'],
            ],
            'is_read' => false,
        ]);
    }

    public function failed(
        string $userId,
        string $runId,
        string $languageName,
        string $languageCode,
        string $collection,
        string $error,
    ): void {
        Notification::create([
            'user_id' => $userId,
            'type' => 'translation_failed',
            'title' => "{$languageName} translation failed",
            'message' => $this->collectionLabel($collection)
                . ': the collection could not be translated. ' . $error,
            'metadata' => [
                'translation_run_id' => $runId,
                'language_code' => $languageCode,
                'collection' => $collection,
            ],
            'is_read' => false,
        ]);
    }

    private function collectionLabel(string $collection): string
    {
        return str($collection)
            ->replace('-', ' ')
            ->title()
            ->toString();
    }
}

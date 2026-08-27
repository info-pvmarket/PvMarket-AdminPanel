<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

final class CompanyDocumentStorageService
{
    public function store(UploadedFile $file, string $companyId): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = now()->timestamp.'-'.Str::uuid().'.'.$extension;
        $path = 'company-documents/'.$companyId.'/'.$filename;
        $mimeType = strtolower((string) (
            $file->getMimeType() ?: $file->getClientMimeType()
        ));

        // FilesystemAdapter::putFileAs() calls getRealPath(), which can return
        // false for otherwise valid PHP upload temp files on Windows/Herd.
        // Opening getPathname() directly also works in Linux containers and
        // keeps the R2 upload streamed instead of loading it into memory.
        $stream = @fopen($file->getPathname(), 'rb');
        if (! is_resource($stream)) {
            throw new RuntimeException('Unable to read the uploaded document.');
        }

        try {
            $stored = Storage::disk('r2')->put(
                $path,
                $stream,
                ['ContentType' => $mimeType],
            );
        } finally {
            fclose($stream);
        }

        if (! $stored) {
            throw new RuntimeException('Unable to upload the document to R2.');
        }

        return [
            'filename' => $path,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'url' => Storage::disk('r2')->url($path),
            'mime_type' => $mimeType,
            'size' => $file->getSize(),
            'uploaded_at' => now(),
        ];
    }

    public function delete(string $path): void
    {
        if ($path !== '') {
            Storage::disk('r2')->delete($path);
        }
    }
}

<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

final class ListingImageService
{
    public function store(UploadedFile $file): array
    {
        $mimeType = strtolower((string) ($file->getMimeType() ?: $file->getClientMimeType()));
        $extension = match ($mimeType) {
            'image/jpeg' => 'jpeg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new RuntimeException('Unsupported listing image type.'),
        };
        $filename = Str::uuid().'.'.$extension;
        $path = Storage::disk('r2')->putFileAs(
            'product-listings',
            $file,
            $filename,
            ['ContentType' => $mimeType],
        );

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Unable to upload the listing image to R2.');
        }

        return [
            'size' => $file->getSize(),
            'uploaded_at' => now()->toISOString(),
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'url' => Storage::disk('r2')->url($path),
            'mime_type' => $mimeType,
            'checksum_sha256' => $this->uploadedFileChecksum($file),
        ];
    }

    public function delete(mixed $image): void
    {
        $path = trim((string) data_get($image, 'path', ''));
        if ($path === '') {
            return;
        }

        Storage::disk('r2')->delete($path);

        // Clean up images created by the legacy Laravel uploader, which wrote
        // to the shared local public volume while storing an R2-shaped URL.
        Storage::disk('public')->delete($path);
    }

    public function uploadedFileChecksum(UploadedFile $file): string
    {
        $checksum = hash_file('sha256', $file->getRealPath());

        if (! is_string($checksum) || $checksum === '') {
            throw new RuntimeException('Unable to fingerprint the listing image.');
        }

        return $checksum;
    }

    public function uploadedFileMetadataSignature(UploadedFile $file): string
    {
        return $this->metadataSignature([
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => strtolower((string) ($file->getMimeType() ?: $file->getClientMimeType())),
        ]);
    }

    public function metadataSignature(mixed $image): string
    {
        return hash('sha256', implode('|', [
            strtolower(trim((string) data_get($image, 'original_name', ''))),
            (string) data_get($image, 'size', ''),
            strtolower(trim((string) data_get($image, 'mime_type', ''))),
        ]));
    }
}

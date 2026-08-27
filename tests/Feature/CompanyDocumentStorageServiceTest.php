<?php

namespace Tests\Feature;

use App\Services\CompanyDocumentStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompanyDocumentStorageServiceTest extends TestCase
{
    #[Test]
    public function it_streams_a_company_document_to_r2_with_api_compatible_metadata(): void
    {
        Storage::fake('r2');
        $file = UploadedFile::fake()->create(
            'company-license.pdf',
            50,
            'application/pdf',
        );

        $metadata = app(CompanyDocumentStorageService::class)->store(
            $file,
            '69fc719433539faafc013d22',
        );

        Storage::disk('r2')->assertExists($metadata['path']);
        $this->assertStringStartsWith(
            'company-documents/69fc719433539faafc013d22/',
            $metadata['path'],
        );
        $this->assertSame($metadata['path'], $metadata['filename']);
        $this->assertSame('company-license.pdf', $metadata['original_name']);
        $this->assertSame('application/pdf', $metadata['mime_type']);
        $this->assertSame(50 * 1024, $metadata['size']);
        $this->assertNotEmpty($metadata['url']);
        $this->assertNotNull($metadata['uploaded_at']);
    }
}

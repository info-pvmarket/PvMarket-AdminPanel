<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminUserDocumentUploadTest extends TestCase
{
    private function projectFile(string $path): string
    {
        return base_path($path);
    }

    #[Test]
    public function admin_user_documents_tab_matches_the_seller_upload_contract(): void
    {
        $view = file_get_contents(
            $this->projectFile('resources/views/admin/users/edit.blade.php')
        );

        $this->assertStringContainsString("route('admin.users.documents.upload'", $view);
        $this->assertStringContainsString('enctype="multipart/form-data"', $view);
        $this->assertStringContainsString('name="document_type"', $view);
        $this->assertStringContainsString('value="Company License"', $view);
        $this->assertStringContainsString('value="VAT/TAX ID"', $view);
        $this->assertStringContainsString('name="documents[]"', $view);
        $this->assertStringContainsString('accept=".jpg,.jpeg,.png,.pdf,.docx"', $view);
        $this->assertStringContainsString('multiple', $view);
        $this->assertStringContainsString('Maximum 10 MB per file.', $view);
    }

    #[Test]
    public function admin_upload_uses_r2_and_creates_pending_company_document_records(): void
    {
        $controller = file_get_contents(
            $this->projectFile('app/Http/Controllers/Admin/UserController.php')
        );
        $routes = file_get_contents($this->projectFile('routes/web.php'));

        $this->assertStringContainsString("name('admin.users.documents.upload')", $routes);
        $this->assertStringContainsString('public function uploadDocuments(', $controller);
        $this->assertStringContainsString("Rule::in(['Company License', 'VAT/TAX ID'])", $controller);
        $this->assertStringContainsString("'mimes:jpg,jpeg,png,pdf,docx'", $controller);
        $this->assertStringContainsString("'max:10240'", $controller);
        $this->assertStringContainsString('CompanyDocumentStorageService $documentStorage', $controller);
        $this->assertStringContainsString('$documentStorage->store(', $controller);
        $this->assertStringContainsString('CompanyDocument::create([', $controller);
        $this->assertStringContainsString("'is_verified' => false", $controller);
        $this->assertStringContainsString("'active_tab' => 'documents'", $controller);
    }
}

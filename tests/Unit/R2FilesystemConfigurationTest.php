<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class R2FilesystemConfigurationTest extends TestCase
{
    private function projectFile(string $path): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function test_r2_disk_uses_credentials_separate_from_amazon_translate(): void
    {
        $filesystems = file_get_contents($this->projectFile('config/filesystems.php'));
        $r2Config = substr($filesystems, strpos($filesystems, "'r2' => ["));

        $this->assertStringContainsString("env('R2_ACCESS_KEY_ID')", $r2Config);
        $this->assertStringContainsString("env('R2_SECRET_ACCESS_KEY')", $r2Config);
        $this->assertStringContainsString("env('R2_REGION', 'auto')", $r2Config);
        $this->assertStringContainsString("env('R2_BUCKET')", $r2Config);
        $this->assertStringContainsString("env('R2_ENDPOINT')", $r2Config);
        $this->assertStringContainsString("env('R2_URL')", $r2Config);
        $this->assertStringContainsString("'use_path_style_endpoint' => true", $r2Config);
        $this->assertStringNotContainsString("env('AWS_ACCESS_KEY_ID')", $r2Config);
        $this->assertStringNotContainsString("env('AWS_SECRET_ACCESS_KEY')", $r2Config);
    }

    public function test_example_environment_documents_the_r2_interface(): void
    {
        $environment = file_get_contents($this->projectFile('.env.example'));

        foreach ([
            'R2_ACCESS_KEY_ID=',
            'R2_SECRET_ACCESS_KEY=',
            'R2_REGION=auto',
            'R2_BUCKET=',
            'R2_ENDPOINT=',
            'R2_URL=',
            'R2_USE_PATH_STYLE_ENDPOINT=true',
        ] as $setting) {
            $this->assertStringContainsString($setting, $environment);
        }
    }
}

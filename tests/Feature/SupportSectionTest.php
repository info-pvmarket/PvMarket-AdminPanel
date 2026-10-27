<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PageSectionController;
use App\Models\PageSection;
use App\Models\PageSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupportSectionTest extends TestCase
{
    private ?string $testDatabase = null;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('A18_MONGODB_TESTS') !== '1') $this->markTestSkipped('Opt in to disposable localhost MongoDB tests.');
        $this->testDatabase = 'pvmarket_a18_test_'.bin2hex(random_bytes(8));
        config(['database.connections.mongodb' => ['driver' => 'mongodb', 'dsn' => 'mongodb://127.0.0.1:27017', 'database' => $this->testDatabase], 'session.driver' => 'array']);
        DB::purge('mongodb');
        $this->withoutMiddleware();
        Http::fake();
    }

    protected function tearDown(): void
    {
        try {
            if ($this->testDatabase && str_starts_with($this->testDatabase, 'pvmarket_a18_test_')) {
                DB::connection('mongodb')->getDatabase()->drop();
                DB::purge('mongodb');
            }
        } finally { parent::tearDown(); }
    }

    public function test_saved_support_and_subsequent_edit_survive_admin_reopen(): void
    {
        $section = PageSection::create(['page' => 'customer_support', 'section' => 'hero', 'type' => 'customer_support', 'title' => 'Original title', 'is_active' => true, 'extra' => []]);
        foreach (['Saved support-first subtitle', 'Later saved support-first subtitle'] as $subtitle) {
            $this->put('/admin/page-sections/global/customer_support', ['is_published' => 1, 'sections' => [(string) $section->getKey() => ['title' => 'Support first', 'subtitle' => $subtitle, 'is_active' => 1, 'extra' => ['email' => 'info@pv.market', 'phone' => '+971 523825549']]]])->assertSessionHasNoErrors();
            $view = app(PageSectionController::class)->edit('global', 'customer_support');
            $reopened = $view->getData()['sections']->first();
            $this->assertSame($subtitle, $reopened->subtitle);
            $this->assertSame('info@pv.market', $reopened->extra['email']);
            $this->assertSame('+971 523825549', $reopened->extra['phone']);
            $this->assertTrue((bool) $reopened->is_active);
            $this->assertTrue((bool) PageSetting::where('page', 'customer_support')->first()->is_published);
        }
    }
}

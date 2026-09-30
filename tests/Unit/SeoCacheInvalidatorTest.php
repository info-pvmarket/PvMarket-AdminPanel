<?php

namespace Tests\Unit;

use App\Services\SeoCacheInvalidator;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SeoCacheInvalidatorTest extends TestCase
{
    public function test_it_revalidates_the_saved_canonical_url(): void
    {
        config()->set('services.frontend.seo_revalidate_url', 'https://pv.market/api/revalidate/seo');
        config()->set('services.frontend.revalidate_secret', 'test-secret');
        Http::fake(['https://pv.market/api/revalidate/seo' => Http::response(['revalidated' => true])]);

        $this->assertTrue((new SeoCacheInvalidator)->invalidate('https://pv.market/ess/dc-freezer-and-refrigerator'));

        Http::assertSent(fn ($request) =>
            $request->url() === 'https://pv.market/api/revalidate/seo'
            && $request->hasHeader('Authorization', 'Bearer test-secret')
            && $request['canonical_url'] === 'https://pv.market/ess/dc-freezer-and-refrigerator'
        );
    }

    public function test_it_skips_records_without_a_canonical_url(): void
    {
        Http::fake();

        $this->assertFalse((new SeoCacheInvalidator)->invalidate(null));
        Http::assertNothingSent();
    }
}

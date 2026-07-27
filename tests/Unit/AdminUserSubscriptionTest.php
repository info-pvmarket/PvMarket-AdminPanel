<?php

namespace Tests\Unit;

use App\Models\Coupon;
use App\Models\Subscription;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class AdminUserSubscriptionTest extends TestCase
{
    private function projectFile(string $path): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function test_coupon_validation_matches_free_subscription_rules(): void
    {
        $now = CarbonImmutable::parse('2026-07-27 12:00:00');
        $coupon = new Coupon([
            'code' => 'FREE2026',
            'type' => 'free',
            'duration_days' => 30,
            'max_uses' => 10,
            'current_uses' => 2,
            'valid_from' => $now->subDay(),
            'valid_until' => $now->addDay(),
            'is_active' => true,
        ]);

        $this->assertNull($coupon->subscriptionValidationError($now));

        $coupon->current_uses = 10;
        $this->assertSame('Coupon usage limit reached.', $coupon->subscriptionValidationError($now));

        $coupon->current_uses = 2;
        $coupon->type = 'discount';
        $this->assertSame(
            'Only free subscription coupons can activate a plan.',
            $coupon->subscriptionValidationError($now)
        );
    }

    public function test_subscription_active_state_includes_its_date_window(): void
    {
        $now = CarbonImmutable::parse('2026-07-27 12:00:00');
        $subscription = new Subscription([
            'status' => 'active',
            'start_date' => $now->subDay(),
            'end_date' => $now->addDay(),
        ]);

        $this->assertTrue($subscription->isActiveAt($now));
        $this->assertSame('active', $subscription->statusForDisplay($now));

        $subscription->end_date = $now->subMinute();
        $this->assertFalse($subscription->isActiveAt($now));
        $this->assertSame('expired', $subscription->statusForDisplay($now));
    }

    public function test_cancelled_subscription_is_not_active(): void
    {
        $now = CarbonImmutable::parse('2026-07-27 12:00:00');
        $subscription = new Subscription([
            'status' => 'cancelled',
            'start_date' => $now->subDay(),
            'end_date' => $now->addDay(),
        ]);

        $this->assertFalse($subscription->isActiveAt($now));
        $this->assertSame('cancelled', $subscription->statusForDisplay($now));
    }

    public function test_admin_user_edit_exposes_subscription_history_and_coupon_activation(): void
    {
        $routes = file_get_contents($this->projectFile('routes/web.php'));
        $controller = file_get_contents(
            $this->projectFile('app/Http/Controllers/Admin/UserController.php')
        );
        $view = file_get_contents($this->projectFile('resources/views/admin/users/edit.blade.php'));
        $service = file_get_contents(
            $this->projectFile('app/Services/AdminSubscriptionService.php')
        );

        $this->assertStringContainsString(
            "name('admin.users.subscriptions.store')",
            $routes
        );
        $this->assertStringContainsString(
            "name('admin.users.subscriptions.cancel')",
            $routes
        );
        $this->assertStringContainsString('subscribeWithCoupon', $controller);
        $this->assertStringContainsString('cancelSubscription', $controller);
        $this->assertStringContainsString("switchTab('subscriptions'", $view);
        $this->assertStringContainsString('Subscription History', $view);
        $this->assertStringContainsString('name="coupon_code"', $view);
        $this->assertStringContainsString('Cancel Subscription', $view);
        $this->assertStringContainsString(
            "route('admin.users.subscriptions.store'",
            $view
        );
        $this->assertStringContainsString(
            "route('admin.users.subscriptions.cancel'",
            $view
        );
        $this->assertStringContainsString("'status' => 'active'", $service);
        $this->assertStringContainsString("'status' => 'cancelled'", $service);
        $this->assertStringContainsString("'cancelled_at' => \$now", $service);
        $this->assertStringContainsString("'payment_status' => 'completed'", $service);
        $this->assertStringContainsString("->increment('current_uses')", $service);
    }
}

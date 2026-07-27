<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use MongoDB\BSON\ObjectId;
use Throwable;

class AdminSubscriptionService
{
    public function subscribeWithCoupon(User $user, string $couponCode, User $admin): Subscription
    {
        $couponCode = strtoupper(trim($couponCode));
        $now = now();
        $userId = new ObjectId((string) $user->_id);

        $coupon = Coupon::where('code', $couponCode)
            ->whereNull('deleted_at')
            ->first();

        if (! $coupon) {
            $this->fail('Coupon not found.');
        }

        if ($message = $coupon->subscriptionValidationError($now)) {
            $this->fail($message);
        }

        $hasActiveSubscription = Subscription::where('user_id', $userId)
            ->where('status', 'active')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->whereNull('deleted_at')
            ->exists();

        if ($hasActiveSubscription) {
            $this->fail('This user already has an active subscription.');
        }

        $hasUsedCoupon = Subscription::where('user_id', $userId)
            ->where('coupon_code', $couponCode)
            ->whereNull('deleted_at')
            ->exists();

        if ($hasUsedCoupon) {
            $this->fail('This user has already used this coupon.');
        }

        $couponQuery = Coupon::where('_id', new ObjectId((string) $coupon->_id))
            ->where('is_active', true)
            ->where('valid_from', '<=', $now)
            ->where('valid_until', '>=', $now)
            ->whereNull('deleted_at');

        if ((int) $coupon->max_uses > 0) {
            $couponQuery->where(function ($query) use ($coupon) {
                $query->where('current_uses', '<', (int) $coupon->max_uses)
                    ->orWhereNull('current_uses');
            });
        }

        if (! $couponQuery->increment('current_uses')) {
            $this->fail('The coupon is no longer available.');
        }

        try {
            return Subscription::create([
                'user_id' => $userId,
                'plan_name' => $coupon->plan_name,
                'products' => (int) $coupon->products,
                'warehouses' => (int) $coupon->warehouses,
                'start_date' => $now,
                'end_date' => $now->copy()->addDays((int) $coupon->duration_days),
                'status' => 'active',
                'coupon_id' => new ObjectId((string) $coupon->_id),
                'coupon_code' => $couponCode,
                'payment_status' => 'completed',
                'amount_paid' => 0.0,
                'currency' => 'USD',
                'is_free_subscription' => true,
                'created_by' => new ObjectId((string) $admin->_id),
            ]);
        } catch (Throwable $exception) {
            Coupon::where('_id', new ObjectId((string) $coupon->_id))
                ->where('current_uses', '>', 0)
                ->decrement('current_uses');

            throw $exception;
        }
    }

    public function cancel(User $user, string $subscriptionId, User $admin): Subscription
    {
        if (! preg_match('/^[a-f\d]{24}$/i', $subscriptionId)) {
            $this->fail('Invalid subscription.', 'subscription_id');
        }

        $now = now();
        $userId = new ObjectId((string) $user->_id);
        $objectId = new ObjectId($subscriptionId);
        $subscription = Subscription::where('_id', $objectId)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->first();

        if (! $subscription) {
            $this->fail('Subscription not found for this user.', 'subscription_id');
        }

        if (! $subscription->isActiveAt($now)) {
            $this->fail('Only an active subscription can be cancelled.', 'subscription_id');
        }

        $cancelled = Subscription::where('_id', $objectId)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->whereNull('deleted_at')
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => $now,
                'cancelled_by' => new ObjectId((string) $admin->_id),
                'updated_at' => $now,
            ]);

        if (! $cancelled) {
            $this->fail(
                'The subscription changed before it could be cancelled. Refresh and try again.',
                'subscription_id'
            );
        }

        $subscription->status = 'cancelled';
        $subscription->cancelled_at = $now;
        $subscription->cancelled_by = new ObjectId((string) $admin->_id);
        $subscription->updated_at = $now;

        return $subscription;
    }

    private function fail(string $message, string $field = 'coupon_code'): never
    {
        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }
}

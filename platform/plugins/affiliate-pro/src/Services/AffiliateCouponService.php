<?php

namespace Botble\AffiliatePro\Services;

use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\AffiliateCoupon;
use Botble\Ecommerce\Enums\DiscountTypeEnum;
use Botble\Ecommerce\Models\Discount;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AffiliateCouponService
{
    public function generateCouponCode(): string
    {
        $prefix = 'AFF';
        $code = $prefix . strtoupper(Str::random(8));

        // Check if code already exists
        while (AffiliateCoupon::query()->where('code', $code)->exists()) {
            $code = $prefix . strtoupper(Str::random(8));
        }

        return $code;
    }

    /**
     * Create a new coupon for an affiliate
     */
    public function createCoupon(
        Affiliate $affiliate,
        float $discountAmount,
        string $discountType = 'percentage',
        ?string $description = null,
        ?Carbon $expiresAt = null
    ): AffiliateCoupon {
        // Generate a unique code
        $code = $this->generateCouponCode();

        // Create the discount in the ecommerce system
        $discount = new Discount();
        $discount->title = 'Affiliate coupon for ' . $affiliate->affiliate_code;
        $discount->code = $code;
        $discount->value = $discountType === 'percentage' ? min($discountAmount, 100) : $discountAmount;
        $discount->type = DiscountTypeEnum::COUPON;
        $discount->type_option = $discountType === 'percentage' ? 'percentage' : 'fixed';
        $discount->can_use_with_promotion = false;
        $discount->quantity = 1000; // Set a high limit
        $discount->product_quantity = 1;
        $discount->start_date = Carbon::now();
        $discount->end_date = $expiresAt;
        $discount->save();

        // Create the affiliate coupon record
        return AffiliateCoupon::query()->create([
            'affiliate_id' => $affiliate->id,
            'discount_id' => $discount->id,
            'code' => $code,
            'description' => $description ?: 'Affiliate discount coupon',
            'discount_amount' => $discountAmount,
            'discount_type' => $discountType,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Get all coupons for an affiliate
     */
    public function getAffiliateCoupons(Affiliate $affiliate)
    {
        return AffiliateCoupon::query()
            ->where('affiliate_id', $affiliate->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Update an existing coupon
     */
    public function updateCoupon(
        AffiliateCoupon $coupon,
        float $discountAmount,
        string $discountType = 'percentage',
        ?string $description = null,
        ?Carbon $expiresAt = null,
        ?string $code = null
    ): AffiliateCoupon {
        // Update the discount in the ecommerce system
        if ($coupon->discount) {
            $discount = $coupon->discount;
            $discount->value = $discountType === 'percentage' ? min($discountAmount, 100) : $discountAmount;
            $discount->type_option = $discountType === 'percentage' ? 'percentage' : 'fixed';

            // Only update the code if it's provided and different from the current one
            if ($code && $code !== $coupon->code) {
                $discount->code = $code;
            }

            $discount->end_date = $expiresAt;
            $discount->save();
        }

        // Update the affiliate coupon record
        $coupon->discount_amount = $discountAmount;
        $coupon->discount_type = $discountType;
        $coupon->description = $description ?: 'Affiliate discount coupon';
        $coupon->expires_at = $expiresAt;

        // Only update the code if it's provided and different from the current one
        if ($code && $code !== $coupon->code) {
            $coupon->code = $code;
        }

        $coupon->save();

        return $coupon;
    }

    /**
     * Delete a coupon
     */
    public function deleteCoupon(AffiliateCoupon $coupon): bool
    {
        // Delete the discount first
        if ($coupon->discount) {
            $coupon->discount->delete();
        }

        // Then delete the coupon
        return $coupon->delete();
    }
}

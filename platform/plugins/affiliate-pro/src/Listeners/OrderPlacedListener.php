<?php

namespace Botble\AffiliatePro\Listeners;

use Botble\AffiliatePro\Enums\CommissionStatusEnum;
use Botble\AffiliatePro\Facades\AffiliateHelper;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\Commission;
use Botble\Ecommerce\Events\OrderPlacedEvent;
use Botble\Ecommerce\Models\OrderProduct;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cookie;

class OrderPlacedListener implements ShouldQueue
{
    public function handle(OrderPlacedEvent $event): void
    {
        $order = $event->order;

        if (Commission::query()->where('order_id', $order->id)->exists()) {
            return;
        }

        $affiliateCode = Cookie::get('affiliate_code');

        if (! $affiliateCode) {
            return;
        }

        $affiliate = Affiliate::query()->where('affiliate_code', $affiliateCode)->first();

        if (! $affiliate) {
            return;
        }

        $totalCommission = 0;

        foreach ($order->products as $orderProduct) {
            $productCommission = $this->calculateProductCommission($orderProduct, $affiliate);
            $totalCommission += $productCommission;
        }

        if ($totalCommission <= 0) {
            return;
        }

        Commission::query()->create([
            'affiliate_id' => $affiliate->id,
            'order_id' => $order->id,
            'amount' => $totalCommission,
            'description' => 'Pending commission for order ' . $order->code,
            'status' => CommissionStatusEnum::PENDING,
        ]);
    }

    protected function calculateProductCommission(OrderProduct $orderProduct, Affiliate $affiliate): float
    {
        $productId = $orderProduct->product_id;
        $productPrice = $orderProduct->price;
        $quantity = $orderProduct->qty;

        $commissionPercentage = AffiliateHelper::getCommissionPercentage($productId, $affiliate);

        return $productPrice * $quantity * ($commissionPercentage / 100);
    }
}

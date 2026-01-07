<?php

namespace Botble\AffiliatePro\Listeners;

use Botble\AffiliatePro\Enums\CommissionStatusEnum;
use Botble\AffiliatePro\Events\CommissionEarnedEvent;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\Commission;
use Botble\Ecommerce\Events\OrderCompletedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderCompletedListener implements ShouldQueue
{
    public function handle(OrderCompletedEvent $event): void
    {
        $order = $event->order;

        $commission = Commission::query()
            ->where('order_id', $order->id)
            ->where('status', CommissionStatusEnum::PENDING)
            ->first();

        if (! $commission) {
            return;
        }

        $affiliate = Affiliate::query()->find($commission->affiliate_id);

        if (! $affiliate) {
            return;
        }

        $commission->update([
            'status' => CommissionStatusEnum::APPROVED,
            'description' => 'Commission approved for order ' . $order->code,
        ]);

        $affiliate->balance += $commission->amount;
        $affiliate->total_commission += $commission->amount;
        $affiliate->save();

        $affiliate->transactions()->create([
            'amount' => $commission->amount,
            'description' => 'Commission approved for order ' . $order->code,
            'type' => 'commission',
            'reference_id' => $commission->id,
            'reference_type' => Commission::class,
        ]);

        event(new CommissionEarnedEvent($commission));
    }
}

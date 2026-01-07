<?php

namespace Botble\AffiliatePro\Listeners;

use Botble\AffiliatePro\Enums\CommissionStatusEnum;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\Commission;
use Botble\Ecommerce\Events\OrderCancelledEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderCancelledListener implements ShouldQueue
{
    public function handle(OrderCancelledEvent $event): void
    {
        $order = $event->order;

        $commission = Commission::query()->where('order_id', $order->id)->first();

        if (! $commission) {
            return;
        }

        if ($commission->status === CommissionStatusEnum::PENDING) {
            $commission->update([
                'status' => CommissionStatusEnum::REJECTED,
                'description' => 'Commission rejected for cancelled order ' . $order->code,
            ]);
        } elseif ($commission->status === CommissionStatusEnum::APPROVED) {
            $affiliate = Affiliate::query()->find($commission->affiliate_id);

            if ($affiliate) {
                $affiliate->balance -= $commission->amount;
                $affiliate->total_commission -= $commission->amount;
                $affiliate->save();

                $affiliate->transactions()->create([
                    'amount' => -$commission->amount,
                    'description' => 'Commission reversed for cancelled order ' . $order->code,
                    'type' => 'reversal',
                    'reference_id' => $commission->id,
                    'reference_type' => Commission::class,
                ]);
            }

            $commission->update([
                'status' => CommissionStatusEnum::REJECTED,
                'description' => 'Commission reversed for cancelled order ' . $order->code,
            ]);
        }
    }
}

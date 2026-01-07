<?php

namespace Botble\AffiliatePro\Listeners;

use Botble\AffiliatePro\Events\WithdrawalRejectedEvent;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Notifications\WithdrawalRejectedNotification;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Customer;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWithdrawalRejectedEmailListener implements ShouldQueue
{
    public function handle(WithdrawalRejectedEvent $event): void
    {
        try {
            $withdrawal = $event->withdrawal;
            $affiliate = Affiliate::query()->find($withdrawal->affiliate_id);

            if (! $affiliate) {
                return;
            }

            /**
             * @var Customer $customer
             */
            $customer = Customer::query()->find($affiliate->customer_id);

            if (! $customer || ! $customer->email) {
                return;
            }

            $rejectionReason = $event->reason ?? 'No specific reason provided.';
            $customer->notify(new WithdrawalRejectedNotification($withdrawal, $rejectionReason));
        } catch (Exception $exception) {
            BaseHelper::logError($exception);
        }
    }
}

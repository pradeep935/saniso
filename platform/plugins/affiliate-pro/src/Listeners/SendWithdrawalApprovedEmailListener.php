<?php

namespace Botble\AffiliatePro\Listeners;

use Botble\AffiliatePro\Events\WithdrawalApprovedEvent;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Notifications\WithdrawalApprovedNotification;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Customer;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWithdrawalApprovedEmailListener implements ShouldQueue
{
    public function handle(WithdrawalApprovedEvent $event): void
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

            $customer->notify(new WithdrawalApprovedNotification($withdrawal));
        } catch (Exception $exception) {
            BaseHelper::logError($exception);
        }
    }
}

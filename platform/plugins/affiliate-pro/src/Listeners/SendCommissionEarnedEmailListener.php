<?php

namespace Botble\AffiliatePro\Listeners;

use Botble\AffiliatePro\Events\CommissionEarnedEvent;
use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Notifications\CommissionEarnedNotification;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Customer;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCommissionEarnedEmailListener implements ShouldQueue
{
    public function handle(CommissionEarnedEvent $event): void
    {
        try {
            $commission = $event->commission;
            $affiliate = Affiliate::query()->find($commission->affiliate_id);

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

            $customer->notify(new CommissionEarnedNotification($commission));
        } catch (Exception $exception) {
            BaseHelper::logError($exception);
        }
    }
}

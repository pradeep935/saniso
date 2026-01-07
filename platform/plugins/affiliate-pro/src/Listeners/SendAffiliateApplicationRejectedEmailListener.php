<?php

namespace Botble\AffiliatePro\Listeners;

use Botble\AffiliatePro\Events\AffiliateApplicationRejectedEvent;
use Botble\AffiliatePro\Notifications\AffiliateApplicationRejectedNotification;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Customer;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAffiliateApplicationRejectedEmailListener implements ShouldQueue
{
    public function handle(AffiliateApplicationRejectedEvent $event): void
    {
        try {
            $affiliate = $event->affiliate;
            /**
             * @var Customer $customer
             */
            $customer = Customer::query()->find($affiliate->customer_id);

            if (! $customer || ! $customer->email) {
                return;
            }

            $customer->notify(new AffiliateApplicationRejectedNotification($affiliate));
        } catch (Exception $exception) {
            BaseHelper::logError($exception);
        }
    }
}

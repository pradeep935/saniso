<?php

namespace Botble\AffiliatePro\Listeners;

use Botble\AffiliatePro\Events\AffiliateApplicationApprovedEvent;
use Botble\AffiliatePro\Notifications\AffiliateApplicationApprovedNotification;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Customer;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAffiliateApplicationApprovedEmailListener implements ShouldQueue
{
    public function handle(AffiliateApplicationApprovedEvent $event): void
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

            $customer->notify(new AffiliateApplicationApprovedNotification($affiliate));
        } catch (Exception $exception) {
            BaseHelper::logError($exception);
        }
    }
}

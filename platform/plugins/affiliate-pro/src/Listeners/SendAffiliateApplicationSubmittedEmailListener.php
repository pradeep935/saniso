<?php

namespace Botble\AffiliatePro\Listeners;

use Botble\AffiliatePro\Events\AffiliateApplicationSubmittedEvent;
use Botble\AffiliatePro\Notifications\AffiliateApplicationSubmittedNotification;
use Botble\Base\Facades\BaseHelper;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendAffiliateApplicationSubmittedEmailListener implements ShouldQueue
{
    public function handle(AffiliateApplicationSubmittedEvent $event): void
    {
        try {
            $adminEmails = get_admin_email()->toArray();

            if (! empty($adminEmails)) {
                Notification::route('mail', $adminEmails)
                    ->notify(new AffiliateApplicationSubmittedNotification($event->affiliate));
            }
        } catch (Exception $exception) {
            BaseHelper::logError($exception);
        }
    }
}

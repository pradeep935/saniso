<?php

namespace Botble\AffiliatePro\Listeners;

use Botble\AffiliatePro\Events\WithdrawalRequestedEvent;
use Botble\AffiliatePro\Notifications\WithdrawalRequestedNotification;
use Botble\Base\Facades\BaseHelper;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendWithdrawalRequestedEmailListener implements ShouldQueue
{
    public function handle(WithdrawalRequestedEvent $event): void
    {
        try {
            $adminEmails = get_admin_email()->toArray();

            if (! empty($adminEmails)) {
                Notification::route('mail', $adminEmails)
                    ->notify(new WithdrawalRequestedNotification($event->withdrawal));
            }
        } catch (Exception $exception) {
            BaseHelper::logError($exception);
        }
    }
}

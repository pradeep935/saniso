<?php

namespace Botble\AffiliatePro\Providers;

use Botble\AffiliatePro\Events\AffiliateApplicationApprovedEvent;
use Botble\AffiliatePro\Events\AffiliateApplicationRejectedEvent;
use Botble\AffiliatePro\Events\AffiliateApplicationSubmittedEvent;
use Botble\AffiliatePro\Events\CommissionEarnedEvent;
use Botble\AffiliatePro\Events\WithdrawalApprovedEvent;
use Botble\AffiliatePro\Events\WithdrawalRejectedEvent;
use Botble\AffiliatePro\Events\WithdrawalRequestedEvent;
use Botble\AffiliatePro\Listeners\OrderCancelledListener;
use Botble\AffiliatePro\Listeners\OrderCompletedListener;
use Botble\AffiliatePro\Listeners\OrderPlacedListener;
use Botble\AffiliatePro\Listeners\RegisterAffiliateWidgets;
use Botble\AffiliatePro\Listeners\SendAffiliateApplicationApprovedEmailListener;
use Botble\AffiliatePro\Listeners\SendAffiliateApplicationRejectedEmailListener;
use Botble\AffiliatePro\Listeners\SendAffiliateApplicationSubmittedEmailListener;
use Botble\AffiliatePro\Listeners\SendAffiliateDigestEmailListener;
use Botble\AffiliatePro\Listeners\SendCommissionEarnedEmailListener;
use Botble\AffiliatePro\Listeners\SendWithdrawalApprovedEmailListener;
use Botble\AffiliatePro\Listeners\SendWithdrawalRejectedEmailListener;
use Botble\AffiliatePro\Listeners\SendWithdrawalRequestedEmailListener;
use Botble\Base\Events\RenderingAdminWidgetEvent;
use Botble\Ecommerce\Events\OrderCancelledEvent;
use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Events\OrderPlacedEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Schedule;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RenderingAdminWidgetEvent::class => [
            RegisterAffiliateWidgets::class,
        ],
        AffiliateApplicationSubmittedEvent::class => [
            SendAffiliateApplicationSubmittedEmailListener::class,
        ],
        AffiliateApplicationApprovedEvent::class => [
            SendAffiliateApplicationApprovedEmailListener::class,
        ],
        AffiliateApplicationRejectedEvent::class => [
            SendAffiliateApplicationRejectedEmailListener::class,
        ],
        CommissionEarnedEvent::class => [
            SendCommissionEarnedEmailListener::class,
        ],
        WithdrawalRequestedEvent::class => [
            SendWithdrawalRequestedEmailListener::class,
        ],
        WithdrawalApprovedEvent::class => [
            SendWithdrawalApprovedEmailListener::class,
        ],
        WithdrawalRejectedEvent::class => [
            SendWithdrawalRejectedEmailListener::class,
        ],
        OrderCompletedEvent::class => [
            OrderCompletedListener::class,
        ],
        OrderCancelledEvent::class => [
            OrderCancelledListener::class,
        ],
        OrderPlacedEvent::class => [
            OrderPlacedListener::class,
        ],
    ];

    public function boot(): void
    {
        $this->app->booted(function () {
            $this->app->afterResolving(Schedule::class, function (Schedule $schedule): void {
                $schedule->command(SendAffiliateDigestEmailListener::class)->weekly()->mondays()->at('08:00');
            });
        });
    }
}

<?php

namespace Botble\AffiliatePro\Listeners;

use Botble\AffiliatePro\Widgets\AffiliateCard;
use Botble\AffiliatePro\Widgets\CommissionCard;
use Botble\AffiliatePro\Widgets\CommissionChart;
use Botble\AffiliatePro\Widgets\PendingCommissionCard;
use Botble\AffiliatePro\Widgets\RecentCommissionsTable;
use Botble\AffiliatePro\Widgets\RecentWithdrawalsTable;
use Botble\AffiliatePro\Widgets\TopAffiliatesTable;
use Botble\AffiliatePro\Widgets\WithdrawalCard;
use Botble\AffiliatePro\Widgets\WithdrawalChart;
use Botble\Base\Events\RenderingAdminWidgetEvent;

class RegisterAffiliateWidgets
{
    public function handle(RenderingAdminWidgetEvent $event): void
    {
        $event->widget
            ->register([
                CommissionCard::class,
                PendingCommissionCard::class,
                AffiliateCard::class,
                WithdrawalCard::class,
                CommissionChart::class,
                WithdrawalChart::class,
                TopAffiliatesTable::class,
                RecentCommissionsTable::class,
                RecentWithdrawalsTable::class,
            ], 'affiliate-pro');
    }
}

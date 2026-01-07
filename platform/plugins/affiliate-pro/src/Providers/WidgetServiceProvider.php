<?php

namespace Botble\AffiliatePro\Providers;

use Botble\AffiliatePro\Widgets\EnhancedReportsWidget;
use Botble\Base\Supports\ServiceProvider;
use Botble\Widget\Facades\WidgetGroup;

class WidgetServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        WidgetGroup::setGroup([
            'id' => 'affiliate-pro',
            'name' => 'Affiliate Pro',
            'widgets' => [
                EnhancedReportsWidget::class,
            ],
        ]);
    }
}

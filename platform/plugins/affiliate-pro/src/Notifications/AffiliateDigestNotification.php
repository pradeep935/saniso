<?php

namespace Botble\AffiliatePro\Notifications;

use Botble\AffiliatePro\Models\Affiliate;
use Botble\Base\Facades\EmailHandler;
use Botble\Ecommerce\Models\Customer;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class AffiliateDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Affiliate $affiliate,
        public array $digestData
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $customer = Customer::query()->find($this->affiliate->customer_id);

        if (! $customer) {
            return new MailMessage();
        }

        $emailHandler = EmailHandler::setModule(AFFILIATE_PRO_MODULE_SCREEN_NAME)
            ->setType('plugins')
            ->setTemplate('affiliate-digest')
            ->addTemplateSettings(AFFILIATE_PRO_MODULE_SCREEN_NAME, config('plugins.affiliate-pro.email', []))
            ->setVariableValues([
                'customer_name' => $customer->name,
                'period_start' => $this->digestData['period_start'] ?? Carbon::now()->subDays(7)->format('M d, Y'),
                'period_end' => $this->digestData['period_end'] ?? Carbon::now()->format('M d, Y'),
                'total_clicks' => number_format($this->digestData['total_clicks'] ?? 0),
                'conversion_rate' => number_format($this->digestData['conversion_rate'] ?? 0, 2),
                'new_commissions_count' => number_format($this->digestData['new_commissions_count'] ?? 0),
                'earnings_this_week' => format_price($this->digestData['earnings_this_week'] ?? 0),
                'current_balance' => format_price($this->affiliate->balance),
                'top_products' => $this->digestData['top_products'] ?? '',
                'traffic_sources' => $this->digestData['traffic_sources'] ?? '',
                'tips' => $this->digestData['tips'] ?? '',
                'affiliate_dashboard_url' => route('affiliate-pro.dashboard'),
                'site_title' => theme_option('site_title', config('app.name')),
            ]);

        return (new MailMessage())
            ->view(['html' => new HtmlString($emailHandler->getContent())])
            ->subject($emailHandler->getSubject());
    }
}

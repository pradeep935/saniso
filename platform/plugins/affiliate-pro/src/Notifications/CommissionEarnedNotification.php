<?php

namespace Botble\AffiliatePro\Notifications;

use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\Commission;
use Botble\Base\Facades\EmailHandler;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class CommissionEarnedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Commission $commission)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $affiliate = Affiliate::query()->find($this->commission->affiliate_id);

        if (! $affiliate) {
            return new MailMessage();
        }

        $customer = Customer::query()->find($affiliate->customer_id);

        if (! $customer) {
            return new MailMessage();
        }

        $emailHandler = EmailHandler::setModule(AFFILIATE_PRO_MODULE_SCREEN_NAME)
            ->setType('plugins')
            ->setTemplate('affiliate-commission-earned')
            ->addTemplateSettings(AFFILIATE_PRO_MODULE_SCREEN_NAME, config('plugins.affiliate-pro.email', []))
            ->setVariableValues([
                'customer_name' => $customer->name,
                'commission_amount' => format_price($this->commission->amount),
                'order_id' => $this->commission->order_id,
                'order_total' => format_price($this->commission->order_amount ?? 0),
                'commission_status' => $this->commission->status->label(),
                'commission_date' => $this->commission->created_at->format('M d, Y'),
                'affiliate_dashboard_url' => route('affiliate-pro.commissions'),
                'current_balance' => format_price($affiliate->balance),
                'site_title' => theme_option('site_title', config('app.name')),
            ]);

        return (new MailMessage())
            ->view(['html' => new HtmlString($emailHandler->getContent())])
            ->subject($emailHandler->getSubject());
    }
}

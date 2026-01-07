<?php

namespace Botble\AffiliatePro\Notifications;

use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\Withdrawal;
use Botble\Base\Facades\EmailHandler;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class WithdrawalRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Withdrawal $withdrawal)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $affiliate = Affiliate::query()->find($this->withdrawal->affiliate_id);

        if (! $affiliate) {
            return new MailMessage();
        }

        $customer = Customer::query()->find($affiliate->customer_id);

        if (! $customer) {
            return new MailMessage();
        }

        $emailHandler = EmailHandler::setModule(AFFILIATE_PRO_MODULE_SCREEN_NAME)
            ->setType('plugins')
            ->setTemplate('affiliate-withdrawal-requested')
            ->addTemplateSettings(AFFILIATE_PRO_MODULE_SCREEN_NAME, config('plugins.affiliate-pro.email', []))
            ->setVariableValues([
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'withdrawal_amount' => format_price($this->withdrawal->amount),
                'withdrawal_method' => $this->withdrawal->payment_method,
                'request_date' => $this->withdrawal->created_at->format('M d, Y'),
                'admin_dashboard_url' => route('affiliate-pro.withdrawals.index'),
                'site_title' => theme_option('site_title', config('app.name')),
            ]);

        return (new MailMessage())
            ->view(['html' => new HtmlString($emailHandler->getContent())])
            ->subject($emailHandler->getSubject());
    }
}

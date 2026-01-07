<?php

namespace Botble\AffiliatePro\Notifications;

use Botble\AffiliatePro\Models\Affiliate;
use Botble\AffiliatePro\Models\Withdrawal;
use Botble\Base\Facades\EmailHandler;
use Botble\Ecommerce\Models\Customer;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class WithdrawalApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Withdrawal $withdrawal, public ?string $transactionId = null)
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
            ->setTemplate('affiliate-withdrawal-approved')
            ->addTemplateSettings(AFFILIATE_PRO_MODULE_SCREEN_NAME, config('plugins.affiliate-pro.email', []))
            ->setVariableValues([
                'customer_name' => $customer->name,
                'withdrawal_amount' => format_price($this->withdrawal->amount),
                'withdrawal_method' => $this->withdrawal->payment_method,
                'processing_date' => Carbon::now()->format('M d, Y'),
                'transaction_id' => $this->transactionId ?: trans('plugins/affiliate-pro::affiliate.email.not_available'),
                'affiliate_dashboard_url' => route('affiliate-pro.withdrawals'),
                'remaining_balance' => format_price($affiliate->balance),
                'site_title' => theme_option('site_title', config('app.name')),
            ]);

        return (new MailMessage())
            ->view(['html' => new HtmlString($emailHandler->getContent())])
            ->subject($emailHandler->getSubject());
    }
}

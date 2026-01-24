<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Ecommerce\Events\OrderPaymentConfirmedEvent;
use Botble\Ecommerce\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Botble\Theme\Facades\Theme;
use RvMedia;
use Carbon\Carbon;

class SendSupplierOrderPdfAfterOrderCompleted implements ShouldQueue
{
    public function handle(OrderPaymentConfirmedEvent $event): void
    {
        $order = $event->order;

        if (! ($order instanceof Order)) {
            return;
        }

        $order->load([
            'products',
            'products.product',
            'products.product.brand',
        ]);


        $supplierEmail = optional(
            optional($order->products->first()->product)->brand
        )->supplier_email;

        $fallbackEmail = 'order@saniso.nl';

        // $toEmail = $supplierEmail ?: $fallbackEmail;
        $toEmail = "pradeepkumar199215pk@gmail.com";

        $logo = get_ecommerce_setting('company_logo_for_invoicing')
            ?: (theme_option('logo_in_invoices') ?: Theme::getLogo());

        $logoUrl = $logo ? RvMedia::getImageUrl(ltrim($logo, './')) : null;

        $order->products->transform(function ($item) {
            $item->product_options = is_string($item->product_options) ? json_decode($item->product_options, true) : $item->product_options;
            return $item;
        });


        $pdf = Pdf::loadView(
            'plugins/ecommerce::orders.supplier-list',[
                'order'   => $order,
                'logoUrl' => $logoUrl,
            ]
        )->setOptions([
            'isRemoteEnabled' => true,
        ]);

        $orderDateTime = Carbon::parse($order->created_at)->format('d-m-Y H:i');
        $orderStatus   = ucfirst($order->status);

        $emailBody = <<<EOT
        Hello,

        Please find attached the supplier order PDF.

        Order details:
        - Order ID: {$order->code}
        - Order Date: {$orderDateTime}
        - Order Status: {$orderStatus}

        Best regards,
        Saniso
        EOT;


        Mail::send([], [], function ($message) use ($pdf, $order, $toEmail, $supplierEmail, $emailBody) {
            $message
                ->to($toEmail)
                ->subject('New Supplier Order - ' . $order->code)
                ->setBody(nl2br($emailBody), 'text/html')
                ->attachData(
                    $pdf->output(),
                    'supplier_order_' . $order->code . '.pdf',
                    ['mime' => 'application/pdf']
                );

            if ($supplierEmail) {
                $message->cc('order@saniso.nl');
            }
        });
    }
}

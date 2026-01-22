<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Botble\Theme\Facades\Theme;
use RvMedia;

class SendSupplierOrderPdfAfterOrderCompleted implements ShouldQueue
{
    public function handle(OrderCompletedEvent $event): void
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

        // $supplierEmail = optional(
        //     optional($order->products->first()->product)->brand
        // )->supplier_email;

        // if (! $supplierEmail) {
        //     return;
        // }

        $supplierEmail = 'devs.pradeep@gmail.com';

        $logo = get_ecommerce_setting('company_logo_for_invoicing')
            ?: (theme_option('logo_in_invoices') ?: Theme::getLogo());

        $logoUrl = $logo
            ? RvMedia::getImageUrl(ltrim($logo, './'))
            : null;

        $order->products->transform(function ($item) {
            $item->product_options = is_string($item->product_options)
                ? json_decode($item->product_options, true)
                : $item->product_options;
            return $item;
        });

        $pdf = Pdf::loadView(
            'plugins/ecommerce::orders.supplier-list',
            [
                'order' => $order,
                'logoUrl'=> $logoUrl,
            ]
        )->setOptions([
            'isRemoteEnabled' => true,
        ]);

        // $cc = 'order@saniso.nl';
        $cc = 'pradeepkumar199215pk@gmail.com';

        Mail::send([], [], function ($message) use ($pdf, $order, $supplierEmail,$cc) {
            $message
                ->to($supplierEmail)
                ->cc($cc)
                ->subject('New Supplier Order - ' . $order->code)
                ->attachData(
                    $pdf->output(),
                    'supplier_order_' . $order->code . '.pdf',
                    ['mime' => 'application/pdf']
                );
        });
    }
}

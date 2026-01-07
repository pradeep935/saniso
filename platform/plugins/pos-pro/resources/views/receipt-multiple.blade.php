@extends('plugins/pos-pro::layouts.master')
@php
    use Illuminate\Support\Arr;
@endphp

@push('header')
    <link rel="stylesheet" href="{{ asset('vendor/core/plugins/pos-pro/css/receipt.css') }}?v=1.0.5">
    <script>
        window.BotbleVariables = window.BotbleVariables || {};
        window.BotbleVariables.languages = window.BotbleVariables.languages || {
            notices_msg: {
                success: "{{ trans('core/base::notices.success') }}",
                error: "{{ trans('core/base::notices.error') }}",
                info: "{{ trans('core/base::notices.info') }}",
                warning: "{{ trans('core/base::notices.warning') }}"
            }
        };
    </script>
@endpush

@section('content')
    <div class="container receipt-container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <x-core::card>
                    <x-core::card.header>
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <x-core::card.title>{{ trans('plugins/pos-pro::pos.receipt') }}</x-core::card.title>
                            <x-core::button type="button" onclick="window.print()">
                                <x-core::icon name="ti ti-printer" class="me-1" /> {{ trans('plugins/pos-pro::pos.print') }}
                            </x-core::button>
                        </div>
                    </x-core::card.header>
                    <x-core::card.body>
                        <div class="receipt-header mb-4">
                            <div class="text-center mb-3">
                                <h2 class="mb-1">{{ get_ecommerce_setting('store_name', config('app.name')) }}</h2>
                                <p class="mb-1">{{ get_ecommerce_setting('store_address') }}</p>
                                @if(get_ecommerce_setting('store_phone'))
                                    <p class="mb-1">{{ trans('plugins/pos-pro::pos.phone') }}: {{ get_ecommerce_setting('store_phone') }}</p>
                                @endif
                                @if(get_ecommerce_setting('store_email'))
                                    <p class="mb-1">{{ trans('plugins/pos-pro::pos.email') }}: {{ get_ecommerce_setting('store_email') }}</p>
                                @endif
                            </div>
                            <hr>
                            <div class="row receipt-info">
                                <div class="col-6 text-start">
                                    @if($orders->isNotEmpty())
                                        <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.date') }}:</strong> {{ BaseHelper::formatDate($orders->first()->created_at) }}</p>
                                    @endif
                                    <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.total_orders') }}:</strong> {{ $orders->count() }}</p>
                                </div>
                                <div class="col-6 text-end">
                                    @php
                                        $firstOrder = $orders->first();
                                    @endphp
                                    @if($firstOrder && $firstOrder->user)
                                        <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.customer') }}:</strong> {{ $firstOrder->user->name }}</p>
                                        @if($firstOrder->user->phone)
                                            <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.phone') }}:</strong> {{ $firstOrder->user->phone }}</p>
                                        @endif
                                        @if($firstOrder->user->email)
                                            <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.email') }}:</strong> {{ $firstOrder->user->email }}</p>
                                        @endif
                                    @elseif($firstOrder && $firstOrder->address && $firstOrder->address->name != 'Guest')
                                        <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.customer') }}:</strong> {{ $firstOrder->address->name }}</p>
                                        @if($firstOrder->address->phone && $firstOrder->address->phone != 'N/A')
                                            <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.phone') }}:</strong> {{ $firstOrder->address->phone }}</p>
                                        @endif
                                        @if($firstOrder->address->email && $firstOrder->address->email != 'guest@example.com')
                                            <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.email') }}:</strong> {{ $firstOrder->address->email }}</p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        @foreach($orders as $order)
                            <div class="vendor-order-section mb-4">
                                @if(is_plugin_active('marketplace') && $order->store_id && $order->store)
                                    <h5 class="mb-3 border-bottom pb-2">
                                        <strong>{{ trans('plugins/pos-pro::pos.vendor') }}:</strong> {{ $order->store->name }}
                                    </h5>
                                @endif
                                
                                <div class="mb-2">
                                    <strong>{{ trans('plugins/pos-pro::pos.order_number') }}:</strong> {{ $order->code }}
                                    @if($order->invoice)
                                        | <strong>{{ trans('plugins/pos-pro::receipt.invoice_code') }}:</strong> {{ $order->invoice->code }}
                                    @endif
                                </div>

                                <div class="table-responsive">
                                    <x-core::table>
                                        <x-core::table.header>
                                            <x-core::table.header.cell>{{ trans('plugins/pos-pro::pos.product') }}</x-core::table.header.cell>
                                            <x-core::table.header.cell class="text-center">{{ trans('plugins/pos-pro::pos.quantity') }}</x-core::table.header.cell>
                                            <x-core::table.header.cell class="text-end">{{ trans('plugins/pos-pro::pos.price') }}</x-core::table.header.cell>
                                            <x-core::table.header.cell class="text-end">{{ trans('plugins/pos-pro::pos.total') }}</x-core::table.header.cell>
                                        </x-core::table.header>
                                        <x-core::table.body>
                                            @foreach($order->products as $product)
                                                <x-core::table.body.row>
                                                    <x-core::table.body.cell>
                                                        <span class="product-name">{{ $product->product_name }}</span>
                                                        @if ($sku = Arr::get($product->options, 'sku') ?: $product->product->sku ?? '')
                                                            <div class="small text-muted">{{ trans('plugins/ecommerce::order.sku') }}: {{ $sku }}</div>
                                                        @endif
                                                        @if ($attributes = Arr::get($product->options, 'attributes'))
                                                            <div class="product-attributes">
                                                                <small><strong>{{ trans('plugins/pos-pro::receipt.attributes') }}:</strong> {{ $attributes }}</small>
                                                            </div>
                                                        @endif
                                                    </x-core::table.body.cell>
                                                    <x-core::table.body.cell class="text-center">{{ $product->qty }}</x-core::table.body.cell>
                                                    <x-core::table.body.cell class="text-end">{{ format_price($product->price) }}</x-core::table.body.cell>
                                                    <x-core::table.body.cell class="text-end">{{ format_price($product->price * $product->qty) }}</x-core::table.body.cell>
                                                </x-core::table.body.row>
                                            @endforeach
                                        </x-core::table.body>
                                    </x-core::table>
                                </div>

                                <div class="receipt-footer mt-3">
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="mb-1">{{ trans('plugins/pos-pro::pos.subtotal') }}</p>
                                            @if($order->discount_amount > 0)
                                                <p class="mb-1">{{ trans('plugins/pos-pro::pos.discount') }}
                                                    @if($order->discount_description)
                                                        ({{ $order->discount_description }})
                                                    @elseif($order->coupon_code)
                                                        ({{ $order->coupon_code }})
                                                    @endif
                                                </p>
                                            @endif
                                            @if($order->shipping_amount > 0)
                                                <p class="mb-1">{{ trans('plugins/pos-pro::pos.shipping') }}</p>
                                            @endif
                                            @if($order->tax_amount > 0)
                                                <p class="mb-1">{{ trans('plugins/pos-pro::pos.tax') }}</p>
                                            @endif
                                            <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.total') }}</strong></p>
                                        </div>
                                        <div class="col-6 text-end">
                                            <p class="mb-1">{{ format_price($order->sub_total) }}</p>
                                            @if($order->discount_amount > 0)
                                                <p class="mb-1">-{{ format_price($order->discount_amount) }}</p>
                                            @endif
                                            @if($order->shipping_amount > 0)
                                                <p class="mb-1">{{ format_price($order->shipping_amount) }}</p>
                                            @endif
                                            @if($order->tax_amount > 0)
                                                <p class="mb-1">{{ format_price($order->tax_amount) }}</p>
                                            @endif
                                            <p class="mb-1"><strong>{{ format_price($order->amount) }}</strong></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="receipt-total-summary mt-4 pt-3 border-top">
                            <div class="row">
                                <div class="col-6">
                                    <h5><strong>{{ trans('plugins/pos-pro::pos.grand_total') }}</strong></h5>
                                </div>
                                <div class="col-6 text-end">
                                    <h5><strong>{{ format_price($orders->sum('amount')) }}</strong></h5>
                                </div>
                            </div>
                        </div>

                        <div class="receipt-footer-info mt-4 pt-3 border-top">
                            <div class="text-center">
                                @php
                                    $firstOrder = $orders->first();
                                @endphp
                                @if($firstOrder && $firstOrder->payment)
                                    <p class="mb-1">{{ trans('plugins/pos-pro::pos.payment_method') }}: {{ $firstOrder->payment->payment_channel->label() ?? 'N/A' }}</p>
                                    <p class="mb-1">{{ trans('plugins/pos-pro::pos.payment_status') }}: {{ $firstOrder->payment->status->label() ?? 'N/A' }}</p>
                                @endif
                                @if($firstOrder && $firstOrder->description)
                                    <p class="mb-1">{{ trans('plugins/pos-pro::pos.notes') }}: {{ $firstOrder->description }}</p>
                                @endif
                                <hr>
                                <p class="mb-1">{{ trans('plugins/pos-pro::pos.thank_you_message') }}</p>
                            </div>
                        </div>
                    </x-core::card.body>
                </x-core::card>
            </div>
        </div>
    </div>
@stop

@push('footer')
    <script src="{{ asset('vendor/core/plugins/pos-pro/js/receipt.js') }}?v=1.0.5"></script>
@endpush
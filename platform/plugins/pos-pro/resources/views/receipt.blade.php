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
                                    <p class="mb-1"><strong>{{ trans('plugins/pos-pro::receipt.invoice_code') }}:</strong> {{ $order->invoice->code }}</p>
                                    <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.order_number') }}:</strong> {{ $order->code }}</p>
                                    <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.date') }}:</strong> {{ BaseHelper::formatDate($order->created_at) }}</p>
                                </div>
                                <div class="col-6 text-end">
                                    @if($order->user)
                                        <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.customer') }}:</strong> {{ $order->user->name }}</p>
                                        @if($order->user->phone)
                                            <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.phone') }}:</strong> {{ $order->user->phone }}</p>
                                        @endif
                                        @if($order->user->email)
                                            <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.email') }}:</strong> {{ $order->user->email }}</p>
                                        @endif
                                    @elseif($order->address && $order->address->name != 'Guest')
                                        <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.customer') }}:</strong> {{ $order->address->name }}</p>
                                        @if($order->address->phone && $order->address->phone != 'N/A')
                                            <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.phone') }}:</strong> {{ $order->address->phone }}</p>
                                        @endif
                                        @if($order->address->email && $order->address->email != 'guest@example.com')
                                            <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.email') }}:</strong> {{ $order->address->email }}</p>
                                        @endif
                                    @endif
                                </div>
                            </div>
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
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>{{ trans('plugins/pos-pro::pos.subtotal') }}:</strong></td>
                                        <td class="text-end">{{ format_price($order->sub_total) }}</td>
                                    </tr>

                                    @if($order->discount_amount > 0)
                                    <tr>
                                        <td colspan="3" class="text-end">
                                            <strong>{{ trans('plugins/pos-pro::pos.discount') }}:</strong>
                                            @if($order->discount_description)
                                                <small class="text-muted d-block">{{ $order->discount_description }}</small>
                                            @endif
                                        </td>
                                        <td class="text-end text-danger">-{{ format_price($order->discount_amount) }}</td>
                                    </tr>
                                    @endif

                                    @if($order->coupon_code)
                                    <tr>
                                        <td colspan="3" class="text-end">
                                            <strong>{{ trans('plugins/pos-pro::pos.coupon') }}:</strong>
                                            <small class="text-muted d-block">{{ $order->coupon_code }}</small>
                                        </td>
                                        <td class="text-end text-danger">
                                            @if($order->discount_amount > 0)
                                                {{ trans('plugins/pos-pro::pos.included_in_discount') }}
                                            @else
                                                -{{ format_price(0) }}
                                            @endif
                                        </td>
                                    </tr>
                                    @endif

                                    @if (EcommerceHelper::isTaxEnabled())
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>{{ trans('plugins/pos-pro::pos.tax') }}:</strong></td>
                                            <td class="text-end">{{ format_price($order->tax_amount) }}</td>
                                        </tr>
                                    @endif

                                    @if($order->shipping_amount > 0)
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>{{ trans('plugins/pos-pro::pos.shipping') }}:</strong></td>
                                        <td class="text-end">{{ format_price($order->shipping_amount) }}</td>
                                    </tr>
                                    @endif

                                    <tr>
                                        <td colspan="3" class="text-end"><strong>{{ trans('plugins/pos-pro::pos.total') }}:</strong></td>
                                        <td class="text-end"><strong>{{ format_price($order->amount) }}</strong></td>
                                    </tr>
                                </tfoot>
                            </x-core::table>
                        </div>

                        <div class="receipt-footer mt-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-sm">
                                        <div class="card-header">
                                            <h5 class="card-title">{{ trans('plugins/pos-pro::pos.payment_details') }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.payment_method') }}:</strong> {!! BaseHelper::clean($order->payment->payment_channel->label()) !!}</p>
                                            <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.payment_status') }}:</strong> {!! BaseHelper::clean($order->payment->status->toHtml()) !!}</p>
                                            @if($order->payment->payment_channel->getValue() == 'pos_card')
                                                <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.card_payment') }}:</strong> {{ trans('plugins/pos-pro::pos.card_payment_details') }}</p>
                                            @endif
                                            @if($order->description)
                                                <p class="mb-1"><strong>{{ trans('plugins/pos-pro::pos.notes') }}:</strong> <span class="text-warning font-bold">{{ $order->description }}</span></p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if($order->shippingAddress && $order->shippingAddress->address)
                                    <div class="col-md-6">
                                        <div class="card card-sm">
                                            <div class="card-header">
                                                <h5 class="card-title">{{ trans('plugins/pos-pro::pos.shipping_address') }}</h5>
                                            </div>
                                            <div class="card-body">
                                                <p class="mb-1"><strong>{{ trans('plugins/pos-pro::receipt.address_name') }}:</strong> {{ $order->shippingAddress->name }}</p>
                                                <p class="mb-1"><strong>{{ trans('plugins/pos-pro::receipt.address') }}:</strong> {{ $order->shippingAddress->address }}</p>
                                                @if($order->shippingAddress->city)
                                                    <p class="mb-1"><strong>{{ trans('plugins/pos-pro::receipt.address_city') }}:</strong> {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }} {{ $order->shippingAddress->zip_code }}</p>
                                                @endif
                                                @if($order->shippingAddress->country_name)
                                                    <p class="mb-1"><strong>{{ trans('plugins/pos-pro::receipt.country') }}:</strong> {{ $order->shippingAddress->country_name }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="text-center mt-4">
                                <p class="fs-5">{{ trans('plugins/pos-pro::pos.thank_you') }}</p>
                                <x-core::button tag="a" href="{{ route('pos-pro.index') }}" class="mt-3">
                                    <x-core::icon name="ti ti-arrow-left" class="me-1" /> {{ trans('plugins/pos-pro::pos.new_order') }}
                                </x-core::button>

                                <x-core::button tag="a" color="primary" href="{{ route('orders.edit', $order->id) }}" class="mt-3" target="_blank">
                                    {{ trans('plugins/pos-pro::pos.view_order', ['code' => $order->code]) }} <x-core::icon name="ti ti-arrow-right" class="ms-1" />
                                </x-core::button>
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

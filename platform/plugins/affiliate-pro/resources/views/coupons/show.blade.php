@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-md-6">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        {{ trans('plugins/affiliate-pro::coupon.coupon_details') }}
                    </x-core::card.title>
                </x-core::card.header>
                <x-core::card.body>
                    <div class="mb-3">
                        <label class="form-label">{{ trans('plugins/affiliate-pro::coupon.code') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $coupon->code }}" readonly id="coupon-code">
                            <button class="btn btn-outline-primary" type="button" data-copy-coupon-code="{{ $coupon->code }}">
                                <x-core::icon name="ti ti-copy" />
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ trans('plugins/affiliate-pro::coupon.affiliate') }}</label>
                        <p class="form-control-static">
                            @if ($coupon->affiliate)
                                <a href="{{ route('affiliate-pro.edit', $coupon->affiliate->id) }}">
                                    {{ $coupon->affiliate->affiliate_code }}
                                </a>
                                <span class="ms-1">
                                    ({{ $coupon->affiliate->customer->name }})
                                </span>
                            @else
                                {{ trans('plugins/affiliate-pro::coupon.no_affiliate') }}
                            @endif
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ trans('plugins/affiliate-pro::coupon.description') }}</label>
                        <p class="form-control-static">{{ $coupon->description ?: '—' }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ trans('plugins/affiliate-pro::coupon.discount') }}</label>
                        <p class="form-control-static">
                            @if ($coupon->discount_type == 'percentage')
                                {{ $coupon->discount_amount }}%
                            @else
                                {{ format_price($coupon->discount_amount) }}
                            @endif
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ trans('plugins/affiliate-pro::coupon.expires_at') }}</label>
                        <p class="form-control-static">
                            @if ($coupon->expires_at)
                                {{ BaseHelper::formatDate($coupon->expires_at) }}
                                @if ($coupon->expires_at->isPast())
                                    <span class="badge bg-danger">{{ trans('plugins/affiliate-pro::coupon.expired') }}</span>
                                @endif
                            @else
                                {{ trans('plugins/affiliate-pro::coupon.no_expiration') }}
                            @endif
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ trans('plugins/affiliate-pro::coupon.created_at') }}</label>
                        <p class="form-control-static">{{ BaseHelper::formatDate($coupon->created_at) }}</p>
                    </div>
                </x-core::card.body>
            </x-core::card>
        </div>

        <div class="col-md-6">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        {{ trans('plugins/affiliate-pro::coupon.usage_details') }}
                    </x-core::card.title>
                </x-core::card.header>
                <x-core::card.body>
                    @if ($coupon->discount && $coupon->discount->usedByCustomers()->count() > 0)
                        <div class="mb-3">
                            <label class="form-label">{{ trans('plugins/affiliate-pro::coupon.times_used') }}</label>
                            <p class="form-control-static">{{ $coupon->discount->usedByCustomers()->count() }}</p>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ trans('plugins/affiliate-pro::coupon.customer') }}</th>
                                        <th>{{ trans('plugins/affiliate-pro::coupon.usage_date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($coupon->discount->usedByCustomers as $customer)
                                        <tr>
                                            <td>
                                                <a href="{{ route('customers.edit', $customer->id) }}">
                                                    {{ $customer->name }}
                                                </a>
                                            </td>
                                            <td>{{ BaseHelper::formatDate($customer->pivot->created_at) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            {{ trans('plugins/affiliate-pro::coupon.no_usage') }}
                        </div>
                    @endif
                </x-core::card.body>
            </x-core::card>
        </div>
    </div>
@endsection

@push('footer')
    <script src="{{ asset('vendor/core/plugins/affiliate-pro/js/front-affiliate.js') }}"></script>
@endpush

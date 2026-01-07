@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-md-12">
            <x-core::card>
                <x-core::card.header>
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <x-core::card.title>
                            <x-core::icon name="ti ti-user-check" class="me-1" />
                            {{ trans('plugins/affiliate-pro::affiliate.view_request', ['id' => $affiliate->id]) }}
                        </x-core::card.title>
                        <x-core::badge color="warning" icon="ti ti-clock">
                            {{ trans('core/base::enums.statuses.pending') }}
                        </x-core::badge>
                    </div>
                </x-core::card.header>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <x-core::card class="h-100 shadow-sm">
                                <x-core::card.header class="border-bottom bg-light">
                                    <x-core::card.title>
                                        <x-core::icon name="ti ti-user" class="me-1" />
                                        {{ trans('plugins/affiliate-pro::affiliate.customer_info') }}
                                    </x-core::card.title>
                                </x-core::card.header>

                                <x-core::card.body>
                                    <x-core::datagrid>
                                        <x-core::datagrid.item>
                                            <x-slot:title>{{ trans('plugins/affiliate-pro::affiliate.customer_name') }}</x-slot:title>
                                            @if ($affiliate->customer)
                                                <a href="{{ route('customers.edit', $affiliate->customer->id) }}" class="text-decoration-none">
                                                    <x-core::icon name="ti ti-external-link" class="me-1" />
                                                    <span class="fw-medium">{{ $affiliate->customer->name }}</span>
                                                </a>
                                            @else
                                                &mdash;
                                            @endif
                                        </x-core::datagrid.item>

                                        <x-core::datagrid.item>
                                            <x-slot:title>{{ trans('plugins/affiliate-pro::affiliate.customer_email') }}</x-slot:title>
                                            @if ($affiliate->customer && $affiliate->customer->email)
                                                <a href="mailto:{{ $affiliate->customer->email }}" class="text-decoration-none">
                                                    <x-core::icon name="ti ti-mail" class="me-1" />
                                                    {{ $affiliate->customer->email }}
                                                </a>
                                            @else
                                                &mdash;
                                            @endif
                                        </x-core::datagrid.item>

                                        <x-core::datagrid.item>
                                            <x-slot:title>{{ trans('plugins/affiliate-pro::affiliate.customer_phone') }}</x-slot:title>
                                            @if ($affiliate->customer && $affiliate->customer->phone)
                                                <a href="tel:{{ $affiliate->customer->phone }}" class="text-decoration-none">
                                                    <x-core::icon name="ti ti-phone" class="me-1" />
                                                    {{ $affiliate->customer->phone }}
                                                </a>
                                            @else
                                                &mdash;
                                            @endif
                                        </x-core::datagrid.item>

                                        @if ($affiliate->customer && $affiliate->customer->addresses && $affiliate->customer->addresses->count() > 0)
                                            <x-core::datagrid.item>
                                                <x-slot:title>{{ trans('plugins/affiliate-pro::affiliate.customer_address') }}</x-slot:title>
                                                @foreach ($affiliate->customer->addresses as $address)
                                                    <div class="mb-2 p-3 border rounded bg-light-subtle">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <strong class="text-dark">{{ $address->name }}</strong>
                                                            @if ($address->is_default)
                                                                <x-core::badge color="primary" icon="ti ti-star">
                                                                    {{ trans('plugins/affiliate-pro::affiliate.default') }}
                                                                </x-core::badge>
                                                            @endif
                                                        </div>
                                                        <div class="text-muted">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <x-core::icon name="ti ti-map-pin" class="me-2 text-primary" />
                                                                {{ $address->address }}, {{ $address->city }}, {{ $address->state }}, {{ $address->country_name }}
                                                            </div>
                                                            @if ($address->phone)
                                                                <div class="d-flex align-items-center mb-1">
                                                                    <x-core::icon name="ti ti-phone" class="me-2 text-primary" />
                                                                    {{ $address->phone }}
                                                                </div>
                                                            @endif
                                                            @if (EcommerceHelper::isZipCodeEnabled() && $address->zip_code)
                                                                <div class="d-flex align-items-center">
                                                                    <x-core::icon name="ti ti-mailbox" class="me-2 text-primary" />
                                                                    {{ $address->zip_code }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </x-core::datagrid.item>
                                        @endif
                                    </x-core::datagrid>
                                </x-core::card.body>
                            </x-core::card>
                        </div>

                        <div class="col-md-6">
                            <x-core::card class="h-100 shadow-sm">
                                <x-core::card.header class="border-bottom bg-light">
                                    <x-core::card.title>
                                        <x-core::icon name="ti ti-share" class="me-1" />
                                        {{ trans('plugins/affiliate-pro::affiliate.affiliate_info') }}
                                    </x-core::card.title>
                                </x-core::card.header>

                                <x-core::card.body>
                                    <x-core::datagrid>
                                        <x-core::datagrid.item>
                                            <x-slot:title>{{ trans('core/base::tables.id') }}</x-slot:title>
                                            {{ $affiliate->id }}
                                        </x-core::datagrid.item>

                                        <x-core::datagrid.item>
                                            <x-slot:title>{{ trans('plugins/affiliate-pro::affiliate.affiliate_code') }}</x-slot:title>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-azure-lt me-2">{{ $affiliate->affiliate_code }}</span>
                                                <x-core::copy :copyableState="$affiliate->affiliate_code" />
                                            </div>
                                        </x-core::datagrid.item>

                                        <x-core::datagrid.item>
                                            <x-slot:title>{{ trans('plugins/affiliate-pro::affiliate.application_status') }}</x-slot:title>
                                            <x-core::badge color="warning" icon="ti ti-clock">
                                                {{ trans('core/base::enums.statuses.pending') }}
                                            </x-core::badge>
                                        </x-core::datagrid.item>

                                        <x-core::datagrid.item>
                                            <x-slot:title>{{ trans('plugins/affiliate-pro::affiliate.application_date') }}</x-slot:title>
                                            <x-core::icon name="ti ti-calendar" class="me-1" />
                                            {{ BaseHelper::formatDateTime($affiliate->created_at) }}
                                        </x-core::datagrid.item>
                                    </x-core::datagrid>
                                </x-core::card.body>
                            </x-core::card>
                        </div>
                    </div>
                </div>

                <x-core::card.footer class="d-flex justify-content-between">
                    <a href="{{ route('affiliate-pro.pending.index') }}" class="btn btn-secondary">
                        <x-core::icon name="ti ti-arrow-left" class="me-1" />
                        {{ trans('plugins/affiliate-pro::affiliate.go_back') }}
                    </a>

                    <div class="d-flex gap-2">
                        <x-core::button
                            type="button"
                            color="success"
                            icon="ti ti-check"
                            data-bs-toggle="modal"
                            data-bs-target="#approve-affiliate-modal"
                        >
                            {{ trans('plugins/affiliate-pro::affiliate.approve') }}
                        </x-core::button>

                        <x-core::button
                            type="button"
                            color="danger"
                            icon="ti ti-x"
                            data-bs-toggle="modal"
                            data-bs-target="#reject-affiliate-modal"
                        >
                            {{ trans('plugins/affiliate-pro::affiliate.reject') }}
                        </x-core::button>
                    </div>
                </x-core::card.footer>
            </x-core::card>
        </div>
    </div>
@stop

@push('footer')
    <x-core::modal.action
        id="approve-affiliate-modal"
        type="success"
        :title="trans('plugins/affiliate-pro::affiliate.approve_affiliate')"
        :description="trans('plugins/affiliate-pro::affiliate.approve_affiliate_confirmation', ['name' => $affiliate->customer ? $affiliate->customer->name : 'ID: ' . $affiliate->id])"
        :has-form="false"
        :submit-button-label="trans('plugins/affiliate-pro::affiliate.approve')"
        :submit-button-attrs="['id' => 'approve-affiliate-button', 'class' => 'btn-success', 'data-url' => route('affiliate-pro.pending.approve', $affiliate->id), 'data-action' => 'approve', 'data-id' => $affiliate->id, 'data-redirect' => route('affiliate-pro.pending.index')]"
        :close-button-label="trans('core/base::forms.cancel')"
    />

    <x-core::modal.action
        id="reject-affiliate-modal"
        type="danger"
        :title="trans('plugins/affiliate-pro::affiliate.reject_affiliate')"
        :description="trans('plugins/affiliate-pro::affiliate.reject_affiliate_confirmation', ['name' => $affiliate->customer ? $affiliate->customer->name : 'ID: ' . $affiliate->id])"
        :has-form="false"
        :submit-button-label="trans('plugins/affiliate-pro::affiliate.reject')"
        :submit-button-attrs="['id' => 'reject-affiliate-button', 'class' => 'btn-danger', 'data-url' => route('affiliate-pro.pending.reject', $affiliate->id), 'data-action' => 'reject', 'data-id' => $affiliate->id, 'data-redirect' => route('affiliate-pro.pending.index')]"
        :close-button-label="trans('core/base::forms.cancel')"
    />
@endpush

@push('scripts')
    {!! Assets::scriptToHtml('affiliate-actions') !!}
    <script src="{{ asset('vendor/core/plugins/affiliate-pro/js/front-affiliate.js') }}"></script>
@endpush

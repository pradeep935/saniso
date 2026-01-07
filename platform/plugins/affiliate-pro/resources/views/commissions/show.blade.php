@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-md-12">
            <x-core::card>
                <x-core::card.header>
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <x-core::card.title>
                            <x-core::icon name="ti ti-coin" class="me-1" />
                            {{ trans('plugins/affiliate-pro::commission.view', ['id' => $commission->id]) }}
                        </x-core::card.title>
                        {{-- Safe: toHtml() returns sanitized HTML via Laravel components --}}
                        {!! $commission->status->toHtml() !!}
                    </div>
                </x-core::card.header>

                <x-core::card.body>
                    <x-core::datagrid>
                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('core/base::tables.id') }}</x-slot:title>
                            {{ $commission->id }}
                        </x-core::datagrid.item>

                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('plugins/affiliate-pro::commission.affiliate') }}</x-slot:title>
                            @if ($commission->affiliate && $commission->affiliate->customer)
                                <a href="{{ route('affiliate-pro.edit', $commission->affiliate->id) }}" class="text-decoration-none">
                                    <x-core::icon name="ti ti-user" class="me-1" />
                                    {{ $commission->affiliate->customer->name }}
                                </a>
                            @else
                                &mdash;
                            @endif
                        </x-core::datagrid.item>

                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('plugins/affiliate-pro::commission.order') }}</x-slot:title>
                            <a href="{{ route('orders.edit', $commission->order_id) }}" class="text-decoration-none">
                                <x-core::icon name="ti ti-shopping-cart" class="me-1" />
                                {{ $commission->order_id }}
                            </a>
                        </x-core::datagrid.item>

                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('plugins/affiliate-pro::commission.amount') }}</x-slot:title>
                            <span class="text-success fw-bold">{{ format_price($commission->amount) }}</span>
                        </x-core::datagrid.item>

                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('plugins/affiliate-pro::commission.description') }}</x-slot:title>
                            {{ $commission->description ?: '&mdash;' }}
                        </x-core::datagrid.item>

                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('plugins/affiliate-pro::commission.status') }}</x-slot:title>
                            {{-- Safe: toHtml() returns sanitized HTML via Laravel components --}}
                            {!! $commission->status->toHtml() !!}
                        </x-core::datagrid.item>

                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('plugins/affiliate-pro::commission.created_at') }}</x-slot:title>
                            <x-core::icon name="ti ti-calendar" class="me-1" />
                            {{ BaseHelper::formatDateTime($commission->created_at) }}
                        </x-core::datagrid.item>
                    </x-core::datagrid>
                </x-core::card.body>

                @if ($commission->status->getValue() === \Botble\AffiliatePro\Enums\CommissionStatusEnum::PENDING)
                    <div class="card-body border-top pt-3">
                        <div class="d-flex gap-2">
                            <x-core::button
                                type="button"
                                color="success"
                                icon="ti ti-check"
                                data-bs-toggle="modal"
                                data-bs-target="#approve-commission-modal"
                            >
                                {{ trans('plugins/affiliate-pro::commission.approve') }}
                            </x-core::button>

                            <x-core::button
                                type="button"
                                color="danger"
                                icon="ti ti-x"
                                data-bs-toggle="modal"
                                data-bs-target="#reject-commission-modal"
                            >
                                {{ trans('plugins/affiliate-pro::commission.reject') }}
                            </x-core::button>
                        </div>
                    </div>
                @endif

                <x-core::card.footer>
                    <a href="{{ route('affiliate-pro.commissions.index') }}" class="btn btn-secondary">
                        <x-core::icon name="ti ti-arrow-left" class="me-1" />
                        {{ trans('plugins/affiliate-pro::affiliate.go_back') }}
                    </a>
                </x-core::card.footer>
            </x-core::card>
        </div>
    </div>
@stop

@push('footer')
    <x-core::modal.action
        id="approve-commission-modal"
        type="success"
        :title="trans('plugins/affiliate-pro::commission.approve_commission')"
        :description="trans('plugins/affiliate-pro::commission.approve_commission_confirmation', ['id' => $commission->id])"
        :has-form="false"
        :submit-button-label="trans('plugins/affiliate-pro::commission.approve')"
        :submit-button-attrs="['id' => 'approve-commission-button', 'class' => 'btn-success', 'data-url' => route('affiliate-pro.commissions.approve', $commission->id), 'data-action' => 'approve', 'data-id' => $commission->id]"
        :close-button-label="trans('core/base::forms.cancel')"
    />

    <x-core::modal.action
        id="reject-commission-modal"
        type="danger"
        :title="trans('plugins/affiliate-pro::commission.reject_commission')"
        :description="trans('plugins/affiliate-pro::commission.reject_commission_confirmation', ['id' => $commission->id])"
        :has-form="false"
        :submit-button-label="trans('plugins/affiliate-pro::commission.reject')"
        :submit-button-attrs="['id' => 'reject-commission-button', 'class' => 'btn-danger', 'data-url' => route('affiliate-pro.commissions.reject', $commission->id), 'data-action' => 'reject', 'data-id' => $commission->id]"
        :close-button-label="trans('core/base::forms.cancel')"
    />
@endpush

@push('scripts')
    {!! Assets::scriptToHtml('affiliate-actions') !!}
@endpush

@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-md-12">
            <x-core::card>
                <x-core::card.header>
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <x-core::card.title>
                            <x-core::icon name="ti ti-cash-banknote" class="me-1" />
                            {{ trans('plugins/affiliate-pro::withdrawal.view', ['id' => $withdrawal->id]) }}
                        </x-core::card.title>
                        @php
                            $statusColor = match($withdrawal->status) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'primary'
                            };
                            $statusIcon = match($withdrawal->status) {
                                'pending' => 'ti ti-clock',
                                'approved' => 'ti ti-check',
                                'rejected' => 'ti ti-x',
                                default => 'ti ti-circle'
                            };
                        @endphp
                        <x-core::badge :color="$statusColor" :icon="$statusIcon">
                            {{ trans("plugins/affiliate-pro::withdrawal.statuses.{$withdrawal->status}") }}
                        </x-core::badge>
                    </div>
                </x-core::card.header>

                <x-core::card.body>
                    <x-core::datagrid>
                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('core/base::tables.id') }}</x-slot:title>
                            {{ $withdrawal->id }}
                        </x-core::datagrid.item>

                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('plugins/affiliate-pro::withdrawal.affiliate') }}</x-slot:title>
                            @if ($withdrawal->affiliate && $withdrawal->affiliate->customer)
                                <a href="{{ route('affiliate-pro.edit', $withdrawal->affiliate->id) }}" class="text-decoration-none">
                                    <x-core::icon name="ti ti-user" class="me-1" />
                                    {{ $withdrawal->affiliate->customer->name }}
                                </a>
                            @else
                                &mdash;
                            @endif
                        </x-core::datagrid.item>

                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('plugins/affiliate-pro::withdrawal.amount') }}</x-slot:title>
                            <span class="text-success fw-bold">{{ format_price($withdrawal->amount) }}</span>
                        </x-core::datagrid.item>

                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('plugins/affiliate-pro::withdrawal.payment_method') }}</x-slot:title>
                            <x-core::icon name="ti ti-credit-card" class="me-1" />
                            {{ $withdrawal->payment_method }}
                        </x-core::datagrid.item>

                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('plugins/affiliate-pro::withdrawal.payment_details') }}</x-slot:title>
                            @if($withdrawal->payment_details)
                                <div class="text-wrap">{{ $withdrawal->payment_details }}</div>
                            @else
                                &mdash;
                            @endif
                        </x-core::datagrid.item>

                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('plugins/affiliate-pro::withdrawal.notes') }}</x-slot:title>
                            @if($withdrawal->notes)
                                <div class="text-wrap">{{ $withdrawal->notes }}</div>
                            @else
                                &mdash;
                            @endif
                        </x-core::datagrid.item>

                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('plugins/affiliate-pro::withdrawal.status') }}</x-slot:title>
                            <x-core::badge :color="$statusColor" :icon="$statusIcon">
                                {{ trans("plugins/affiliate-pro::withdrawal.statuses.{$withdrawal->status}") }}
                            </x-core::badge>
                        </x-core::datagrid.item>

                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('plugins/affiliate-pro::withdrawal.created_at') }}</x-slot:title>
                            <x-core::icon name="ti ti-calendar" class="me-1" />
                            {{ BaseHelper::formatDateTime($withdrawal->created_at) }}
                        </x-core::datagrid.item>
                    </x-core::datagrid>
                </x-core::card.body>

                @if ($withdrawal->status == 'pending')
                    <div class="card-body border-top pt-3">
                        <div class="d-flex gap-2">
                            <x-core::button
                                type="button"
                                color="success"
                                icon="ti ti-check"
                                data-bs-toggle="modal"
                                data-bs-target="#approve-withdrawal-modal"
                            >
                                {{ trans('plugins/affiliate-pro::withdrawal.approve') }}
                            </x-core::button>

                            <x-core::button
                                type="button"
                                color="danger"
                                icon="ti ti-x"
                                data-bs-toggle="modal"
                                data-bs-target="#reject-withdrawal-modal"
                            >
                                {{ trans('plugins/affiliate-pro::withdrawal.reject') }}
                            </x-core::button>
                        </div>
                    </div>
                @endif

                <x-core::card.footer>
                    <a href="{{ route('affiliate-pro.withdrawals.index') }}" class="btn btn-secondary">
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
        id="approve-withdrawal-modal"
        type="success"
        :title="trans('plugins/affiliate-pro::withdrawal.approve_withdrawal')"
        :description="trans('plugins/affiliate-pro::withdrawal.approve_withdrawal_confirmation', ['id' => $withdrawal->id])"
        :has-form="false"
        :submit-button-label="trans('plugins/affiliate-pro::withdrawal.approve')"
        :submit-button-attrs="['id' => 'approve-withdrawal-button', 'class' => 'btn-success', 'data-url' => route('affiliate-pro.withdrawals.approve', $withdrawal->id), 'data-action' => 'approve', 'data-id' => $withdrawal->id]"
        :close-button-label="trans('core/base::forms.cancel')"
    />

    <x-core::modal.action
        id="reject-withdrawal-modal"
        type="danger"
        :title="trans('plugins/affiliate-pro::withdrawal.reject_withdrawal')"
        :description="trans('plugins/affiliate-pro::withdrawal.reject_withdrawal_confirmation', ['id' => $withdrawal->id])"
        :has-form="false"
        :submit-button-label="trans('plugins/affiliate-pro::withdrawal.reject')"
        :submit-button-attrs="['id' => 'reject-withdrawal-button', 'class' => 'btn-danger', 'data-url' => route('affiliate-pro.withdrawals.reject', $withdrawal->id), 'data-action' => 'reject', 'data-id' => $withdrawal->id]"
        :close-button-label="trans('core/base::forms.cancel')"
    />
@endpush

@push('scripts')
    {!! Assets::scriptToHtml('affiliate-actions') !!}
@endpush

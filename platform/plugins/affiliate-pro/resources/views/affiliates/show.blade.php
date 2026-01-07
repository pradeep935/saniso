@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="container-fluid">
        <div class="page-header mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        {{ trans('plugins/affiliate-pro::affiliate.name') }}
                    </div>
                    <h2 class="page-title">
                        {{ $affiliate->customer?->name ?: $affiliate->affiliate_code }}
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <div class="btn-list">
                        @php
                            $statusColor = match($affiliate->status->getValue()) {
                                'pending' => 'yellow',
                                'active' => 'green',
                                'inactive' => 'secondary',
                                'banned' => 'red',
                                default => 'blue'
                            };
                        @endphp
                        <span class="badge bg-{{ $statusColor }}-lt">
                            {{ $affiliate->status->label() }}
                        </span>
                        <a href="{{ route('affiliate-pro.edit', $affiliate->id) }}" class="btn btn-primary">
                            <x-core::icon name="ti ti-edit" />
                            {{ trans('core/base::forms.edit') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Financial Summary Cards --}}
            <div class="col-12">
                <div class="row g-3">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="bg-primary text-white avatar">
                                            <x-core::icon name="ti ti-wallet" />
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="text-secondary">
                                            {{ trans('plugins/affiliate-pro::affiliate.balance') }}
                                        </div>
                                        <div class="h1 mb-0">{{ format_price($affiliate->balance) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="bg-success text-white avatar">
                                            <x-core::icon name="ti ti-coin" />
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="text-secondary">
                                            {{ trans('plugins/affiliate-pro::affiliate.total_commission') }}
                                        </div>
                                        <div class="h1 mb-0">{{ format_price($affiliate->total_commission) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="bg-info text-white avatar">
                                            <x-core::icon name="ti ti-cash" />
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="text-secondary">
                                            {{ trans('plugins/affiliate-pro::affiliate.total_withdrawn') }}
                                        </div>
                                        <div class="h1 mb-0">{{ format_price($affiliate->total_withdrawn) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="bg-yellow text-white avatar">
                                            <x-core::icon name="ti ti-percentage" />
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="text-secondary">
                                            {{ trans('plugins/affiliate-pro::affiliate.conversion_rate') }}
                                        </div>
                                        <div class="h1 mb-0">
                                            @php
                                                $totalClicks = $affiliate->tracking->count();
                                                $conversions = $affiliate->tracking->where('converted', true)->count();
                                                $conversionRate = $totalClicks > 0 ? round(($conversions / $totalClicks) * 100, 2) : 0;
                                            @endphp
                                            {{ $conversionRate }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Affiliate Information Card --}}
            <div class="col-lg-8">
                <x-core::card>
                    <x-core::card.header>
                        <x-core::card.title>
                            {{ trans('plugins/affiliate-pro::affiliate.affiliate_info') }}
                        </x-core::card.title>
                    </x-core::card.header>
                    <x-core::card.body>
                        <div class="datagrid">
                            <div class="datagrid-item">
                                <div class="datagrid-title">{{ trans('core/base::tables.id') }}</div>
                                <div class="datagrid-content">
                                    <span class="badge bg-azure text-azure-fg">#{{ $affiliate->id }}</span>
                                </div>
                            </div>

                            <div class="datagrid-item">
                                <div class="datagrid-title">{{ trans('plugins/affiliate-pro::affiliate.customer') }}</div>
                                <div class="datagrid-content">
                                    @if ($affiliate->customer)
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-xs me-2 avatar-rounded">
                                                {{ strtoupper(substr($affiliate->customer->name, 0, 2)) }}
                                            </span>
                                            <div>
                                                <a href="{{ route('customers.edit', $affiliate->customer->id) }}" class="text-reset">
                                                    {{ $affiliate->customer->name }}
                                                </a>
                                                <div class="text-secondary">{{ $affiliate->customer->email }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </div>
                            </div>

                            <div class="datagrid-item">
                                <div class="datagrid-title">{{ trans('plugins/affiliate-pro::affiliate.affiliate_code') }}</div>
                                <div class="datagrid-content">
                                    <div class="d-flex align-items-center gap-2">
                                        <code class="text-primary">{{ $affiliate->affiliate_code }}</code>
                                        <x-core::copy 
                                            copyableState="{{ $affiliate->affiliate_code }}"
                                            :tooltip="trans('core/base::forms.copy')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="datagrid-item">
                                <div class="datagrid-title">{{ trans('plugins/affiliate-pro::affiliate.affiliate_link') }}</div>
                                <div class="datagrid-content">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-break small">{{ url('?ref=' . $affiliate->affiliate_code) }}</span>
                                        <x-core::copy 
                                            copyableState="{{ url('?ref=' . $affiliate->affiliate_code) }}"
                                            :tooltip="trans('core/base::forms.copy')"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="datagrid-item">
                                <div class="datagrid-title">{{ trans('core/base::tables.created_at') }}</div>
                                <div class="datagrid-content">
                                    <x-core::icon name="ti ti-calendar" class="text-secondary me-1" />
                                    {{ BaseHelper::formatDateTime($affiliate->created_at) }}
                                </div>
                            </div>
                        </div>
                    </x-core::card.body>
                </x-core::card>
            </div>

            {{-- Quick Actions Card --}}
            <div class="col-lg-4">
                <x-core::card>
                    <x-core::card.header>
                        <x-core::card.title>
                            {{ trans('plugins/affiliate-pro::affiliate.quick_actions') }}
                        </x-core::card.title>
                    </x-core::card.header>
                    <x-core::card.body>
                        <div class="d-grid gap-2">
                            @if ($affiliate->status != \Botble\AffiliatePro\Enums\AffiliateStatusEnum::BANNED)
                                <x-core::button
                                    type="button"
                                    color="danger"
                                    icon="ti ti-lock"
                                    class="w-100"
                                    data-bb-toggle="confirm-action"
                                    data-bb-message="{{ trans('plugins/affiliate-pro::affiliate.ban_confirmation_generic') }}"
                                    onclick="event.preventDefault(); document.getElementById('ban-form').submit();"
                                >
                                    {{ trans('plugins/affiliate-pro::affiliate.ban') }}
                                </x-core::button>
                                <form id="ban-form" action="{{ route('affiliate-pro.ban', $affiliate) }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            @else
                                <x-core::button
                                    type="button"
                                    color="success"
                                    icon="ti ti-lock-open"
                                    class="w-100"
                                    data-bb-toggle="confirm-action"
                                    data-bb-message="{{ trans('plugins/affiliate-pro::affiliate.unban_confirmation_generic') }}"
                                    onclick="event.preventDefault(); document.getElementById('unban-form').submit();"
                                >
                                    {{ trans('plugins/affiliate-pro::affiliate.unban') }}
                                </x-core::button>
                                <form id="unban-form" action="{{ route('affiliate-pro.unban', $affiliate) }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            @endif

                            <a href="{{ route('affiliate-pro.commissions.index', ['filter_table_id' => 'botble-affiliate-pro-tables-commission-table', 'filter_key' => 'affiliate_id', 'filter_operator' => '=', 'filter_value' => $affiliate->id]) }}" class="btn btn-outline-primary w-100">
                                <x-core::icon name="ti ti-coin" class="me-1" />
                                {{ trans('plugins/affiliate-pro::affiliate.view_all_commissions') }}
                            </a>

                            <a href="{{ route('affiliate-pro.withdrawals.index', ['filter_table_id' => 'botble-affiliate-pro-tables-withdrawal-table', 'filter_key' => 'affiliate_id', 'filter_operator' => '=', 'filter_value' => $affiliate->id]) }}" class="btn btn-outline-info w-100">
                                <x-core::icon name="ti ti-cash-banknote" class="me-1" />
                                {{ trans('plugins/affiliate-pro::affiliate.view_all_withdrawals') }}
                            </a>
                        </div>
                    </x-core::card.body>
                </x-core::card>
            </div>

            {{-- Activity Tabs --}}
            <div class="col-12">
                <x-core::card>
                    <x-core::card.header>
                        <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a href="#tab-commissions" class="nav-link active" data-bs-toggle="tab" role="tab">
                                    <x-core::icon name="ti ti-coin" class="me-1" />
                                    {{ trans('plugins/affiliate-pro::affiliate.commissions') }}
                                    <span class="badge bg-blue text-blue-fg ms-2">{{ $affiliate->commissions->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a href="#tab-withdrawals" class="nav-link" data-bs-toggle="tab" role="tab">
                                    <x-core::icon name="ti ti-cash-banknote" class="me-1" />
                                    {{ trans('plugins/affiliate-pro::affiliate.withdrawals') }}
                                    <span class="badge bg-green text-green-fg ms-2">{{ $affiliate->withdrawals->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a href="#tab-tracking" class="nav-link" data-bs-toggle="tab" role="tab">
                                    <x-core::icon name="ti ti-click" class="me-1" />
                                    {{ trans('plugins/affiliate-pro::affiliate.tracking') }}
                                    <span class="badge bg-cyan text-cyan-fg ms-2">{{ $affiliate->tracking->count() }}</span>
                                </a>
                            </li>
                        </ul>
                    </x-core::card.header>
                    <x-core::card.body>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab-commissions" role="tabpanel">
                                @if($affiliate->commissions->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-vcenter table-striped">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('plugins/affiliate-pro::commission.order') }}</th>
                                                    <th>{{ trans('plugins/affiliate-pro::commission.amount') }}</th>
                                                    <th>{{ trans('plugins/affiliate-pro::commission.type') }}</th>
                                                    <th>{{ trans('plugins/affiliate-pro::commission.status') }}</th>
                                                    <th>{{ trans('core/base::tables.created_at') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($affiliate->commissions->take(10) as $commission)
                                                    <tr>
                                                        <td>
                                                            @if($commission->order)
                                                                <a href="{{ route('orders.edit', $commission->order->id) }}">
                                                                    {{ $commission->order->code }}
                                                                </a>
                                                            @else
                                                                <span class="text-secondary">—</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <strong class="text-success">{{ format_price($commission->amount) }}</strong>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-indigo text-indigo-fg">{{ $commission->type }}</span>
                                                        </td>
                                                        <td>
                                                            @php
                                                                $commissionStatusColor = match($commission->status) {
                                                                    'pending' => 'yellow',
                                                                    'approved' => 'green',
                                                                    'rejected' => 'red',
                                                                    default => 'secondary'
                                                                };
                                                            @endphp
                                                            <span class="badge bg-{{ $commissionStatusColor }}-lt">
                                                                {{ trans("plugins/affiliate-pro::commission.statuses.{$commission->status}") }}
                                                            </span>
                                                        </td>
                                                        <td class="text-secondary">
                                                            {{ BaseHelper::formatDateTime($commission->created_at) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="empty">
                                        <div class="empty-icon">
                                            <x-core::icon name="ti ti-coin-off" style="font-size: 3rem;" class="text-secondary" />
                                        </div>
                                        <p class="empty-title">{{ trans('plugins/affiliate-pro::affiliate.no_commissions') }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="tab-pane" id="tab-withdrawals" role="tabpanel">
                                @if($affiliate->withdrawals->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-vcenter table-striped">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('core/base::tables.id') }}</th>
                                                    <th>{{ trans('plugins/affiliate-pro::withdrawal.amount') }}</th>
                                                    <th>{{ trans('plugins/affiliate-pro::withdrawal.payment_method') }}</th>
                                                    <th>{{ trans('plugins/affiliate-pro::withdrawal.status') }}</th>
                                                    <th>{{ trans('core/base::tables.created_at') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($affiliate->withdrawals->take(10) as $withdrawal)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('affiliate-pro.withdrawals.show', $withdrawal->id) }}">
                                                                #{{ $withdrawal->id }}
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <strong class="text-success">{{ format_price($withdrawal->amount) }}</strong>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-purple text-purple-fg">{{ $withdrawal->payment_method }}</span>
                                                        </td>
                                                        <td>
                                                            @php
                                                                $withdrawalStatusColor = match($withdrawal->status) {
                                                                    'pending' => 'yellow',
                                                                    'approved' => 'green',
                                                                    'rejected' => 'red',
                                                                    default => 'secondary'
                                                                };
                                                            @endphp
                                                            <span class="badge bg-{{ $withdrawalStatusColor }}-lt">
                                                                {{ trans("plugins/affiliate-pro::withdrawal.statuses.{$withdrawal->status}") }}
                                                            </span>
                                                        </td>
                                                        <td class="text-secondary">
                                                            {{ BaseHelper::formatDateTime($withdrawal->created_at) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="empty">
                                        <div class="empty-icon">
                                            <x-core::icon name="ti ti-cash-off" style="font-size: 3rem;" class="text-secondary" />
                                        </div>
                                        <p class="empty-title">{{ trans('plugins/affiliate-pro::affiliate.no_withdrawals') }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="tab-pane" id="tab-tracking" role="tabpanel">
                                @if($affiliate->tracking->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-vcenter table-striped">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('plugins/affiliate-pro::affiliate.ip_address') }}</th>
                                                    <th>{{ trans('plugins/affiliate-pro::affiliate.referrer_url') }}</th>
                                                    <th>{{ trans('plugins/affiliate-pro::affiliate.landing_page') }}</th>
                                                    <th>{{ trans('plugins/affiliate-pro::affiliate.converted') }}</th>
                                                    <th>{{ trans('core/base::tables.created_at') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($affiliate->tracking->take(10) as $track)
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-teal text-teal-fg">{{ $track->ip_address ?: '—' }}</span>
                                                        </td>
                                                        <td>
                                                            @if($track->referrer_url)
                                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $track->referrer_url }}">
                                                                    {{ $track->referrer_url }}
                                                                </span>
                                                            @else
                                                                <span class="text-secondary">—</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($track->landing_url)
                                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $track->landing_url }}">
                                                                    {{ $track->landing_url }}
                                                                </span>
                                                            @else
                                                                <span class="text-secondary">—</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($track->converted)
                                                                <span class="badge bg-green-lt">
                                                                    <x-core::icon name="ti ti-check" class="me-1" />
                                                                    {{ trans('plugins/affiliate-pro::affiliate.yes') }}
                                                                </span>
                                                            @else
                                                                <span class="badge bg-secondary-lt">
                                                                    <x-core::icon name="ti ti-x" class="me-1" />
                                                                    {{ trans('plugins/affiliate-pro::affiliate.no') }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="text-secondary">
                                                            {{ BaseHelper::formatDateTime($track->created_at) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($affiliate->tracking->count() > 10)
                                        <div class="card-footer">
                                            <p class="text-secondary mb-0">
                                                {{ trans('plugins/affiliate-pro::affiliate.showing_recent_tracking', ['count' => 10]) }}
                                            </p>
                                        </div>
                                    @endif
                                @else
                                    <div class="empty">
                                        <div class="empty-icon">
                                            <x-core::icon name="ti ti-click" style="font-size: 3rem;" class="text-secondary" />
                                        </div>
                                        <p class="empty-title">{{ trans('plugins/affiliate-pro::affiliate.no_tracking') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </x-core::card.body>
                </x-core::card>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('affiliate-pro.index') }}" class="btn btn-secondary">
                <x-core::icon name="ti ti-arrow-left" class="me-1" />
                {{ trans('plugins/affiliate-pro::affiliate.go_back') }}
            </a>
        </div>
    </div>
@stop
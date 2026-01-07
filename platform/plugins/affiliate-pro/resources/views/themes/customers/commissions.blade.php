@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', trans('plugins/affiliate-pro::commission.history'))

@section('content')
    <div class="affiliate-dashboard">
        <!-- Page Header -->
        <div class="affiliate-card mb-4">
            <div class="affiliate-card-header">
                <div class="affiliate-card-title">
                    <x-core::icon name="ti ti-coins" class="me-2" />
                    {{ trans('plugins/affiliate-pro::commission.history') }}
                </div>
                <div class="affiliate-card-status">
                    <span class="badge bg-primary">
                        {{ trans('plugins/affiliate-pro::commission.total_commissions', ['count' => $commissions->total()]) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="affiliate-card mb-4">
            <div class="affiliate-card-body">
                <form method="GET" action="{{ route('affiliate-pro.commissions') }}" class="commission-filters">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">{{ trans('plugins/affiliate-pro::commission.filter_by_status') }}</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">{{ trans('plugins/affiliate-pro::commission.all_statuses') }}</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                    {{ trans('plugins/affiliate-pro::commission.statuses.pending') }}
                                </option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                                    {{ trans('plugins/affiliate-pro::commission.statuses.approved') }}
                                </option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                    {{ trans('plugins/affiliate-pro::commission.statuses.rejected') }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">{{ trans('plugins/affiliate-pro::commission.date_from') }}</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">{{ trans('plugins/affiliate-pro::commission.date_to') }}</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <x-core::icon name="ti ti-filter" class="me-1" />
                                    {{ trans('plugins/affiliate-pro::commission.filter') }}
                                </button>
                                <a href="{{ route('affiliate-pro.commissions') }}" class="btn btn-outline-secondary">
                                    <x-core::icon name="ti ti-x" class="me-1" />
                                    {{ trans('plugins/affiliate-pro::commission.clear') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Commissions List -->
        <div class="affiliate-card">
            <div class="affiliate-card-body">
                @if(count($commissions) > 0)
                    <!-- Summary Stats -->
                    <div class="commission-summary mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="summary-card">
                                    <div class="summary-icon bg-success">
                                        <x-core::icon name="ti ti-check" />
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-value">{{ format_price($commissions->where('status', 'approved')->sum('amount')) }}</div>
                                        <div class="summary-label">{{ trans('plugins/affiliate-pro::commission.approved_earnings') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card">
                                    <div class="summary-icon bg-warning">
                                        <x-core::icon name="ti ti-clock" />
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-value">{{ format_price($commissions->where('status', 'pending')->sum('amount')) }}</div>
                                        <div class="summary-label">{{ trans('plugins/affiliate-pro::commission.pending_earnings') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card">
                                    <div class="summary-icon bg-primary">
                                        <x-core::icon name="ti ti-coins" />
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-value">{{ $commissions->where('status', 'approved')->count() }}</div>
                                        <div class="summary-label">{{ trans('plugins/affiliate-pro::commission.successful_orders') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-card">
                                    <div class="summary-icon bg-info">
                                        <x-core::icon name="ti ti-percentage" />
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-value">
                                            {{ $commissions->count() > 0 ? number_format(($commissions->where('status', 'approved')->count() / $commissions->count()) * 100, 1) : 0 }}%
                                        </div>
                                        <div class="summary-label">{{ trans('plugins/affiliate-pro::commission.approval_rate') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Commission Items -->
                    <div class="commission-list">
                        @foreach($commissions as $commission)
                            <div class="commission-item-detailed">
                                <div class="commission-header">
                                    <div class="commission-ids">
                                        <div class="commission-id">
                                            <x-core::icon name="ti ti-hash" class="me-1 text-muted" />
                                            <span class="fw-medium">{{ trans('plugins/affiliate-pro::commission.commission_id') }} #{{ $commission->id }}</span>
                                        </div>
                                        <div class="order-id">
                                            <x-core::icon name="ti ti-shopping-cart" class="me-1 text-muted" />
                                            <span>{{ trans('plugins/affiliate-pro::commission.order_id') }} #{{ $commission->order_id }}</span>
                                        </div>
                                    </div>
                                    <div class="commission-status">
                                        @if($commission->status == 'pending')
                                            <span class="badge bg-warning text-dark">
                                                <x-core::icon name="ti ti-clock" class="me-1" />
                                                {{ trans('plugins/affiliate-pro::commission.statuses.pending') }}
                                            </span>
                                        @elseif($commission->status == 'approved')
                                            <span class="badge bg-success text-white">
                                                <x-core::icon name="ti ti-check" class="me-1" />
                                                {{ trans('plugins/affiliate-pro::commission.statuses.approved') }}
                                            </span>
                                        @elseif($commission->status == 'rejected')
                                            <span class="badge bg-danger text-white">
                                                <x-core::icon name="ti ti-x" class="me-1" />
                                                {{ trans('plugins/affiliate-pro::commission.statuses.rejected') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="commission-content">
                                    <div class="commission-amount-large">
                                        <span class="amount-value">{{ format_price($commission->amount) }}</span>
                                        <small class="text-muted">{{ trans('plugins/affiliate-pro::commission.commission_earned') }}</small>
                                    </div>
                                    <div class="commission-details-expanded">
                                        @if($commission->description)
                                            <div class="commission-description">
                                                <x-core::icon name="ti ti-file-text" class="me-1 text-muted" />
                                                <span>{{ $commission->description }}</span>
                                            </div>
                                        @endif
                                        <div class="commission-date">
                                            <x-core::icon name="ti ti-calendar" class="me-1 text-muted" />
                                            <span>{{ $commission->created_at->translatedFormat('M d, Y \a\t H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($commissions->hasPages())
                        <div class="commission-pagination mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="pagination-info">
                                    <small class="text-muted">
                                        {{ trans('plugins/affiliate-pro::commission.showing_results', [
                                            'from' => $commissions->firstItem(),
                                            'to' => $commissions->lastItem(),
                                            'total' => $commissions->total()
                                        ]) }}
                                    </small>
                                </div>
                                <div class="pagination-links">
                                    {!! $commissions->appends(request()->query())->links() !!}
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="empty-state text-center py-5">
                        <div class="empty-state-icon mb-3">
                            <x-core::icon name="ti ti-coins" class="text-muted" style="font-size: 4rem;" />
                        </div>
                        <h5 class="text-muted mb-3">{{ trans('plugins/affiliate-pro::commission.no_commissions') }}</h5>
                        <p class="text-muted mb-4">{{ trans('plugins/affiliate-pro::commission.no_commissions_description') }}</p>
                        <div class="empty-state-actions">
                            <a href="{{ route('affiliate-pro.materials') }}" class="btn btn-primary">
                                <x-core::icon name="ti ti-rocket" class="me-1" />
                                {{ trans('plugins/affiliate-pro::commission.start_promoting') }}
                            </a>
                            <a href="{{ route('affiliate-pro.dashboard') }}" class="btn btn-outline-secondary ms-2">
                                <x-core::icon name="ti ti-arrow-left" class="me-1" />
                                {{ trans('plugins/affiliate-pro::commission.back_to_dashboard') }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

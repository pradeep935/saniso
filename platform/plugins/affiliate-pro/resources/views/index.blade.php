@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0">{{ number_format($totalAffiliates) }}</h4>
                    <p class="mb-0">{{ trans('plugins/affiliate-pro::affiliate.name') }}</p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('affiliate-pro.index') }}">{{ trans('core/base::tables.view_all') }}</a>
                    <div class="small text-white"><x-core::icon name="ti ti-chevron-right" /></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0">{{ format_price($totalCommission) }}</h4>
                    <p class="mb-0">{{ trans('plugins/affiliate-pro::commission.name') }}</p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('affiliate-pro.commissions.index') }}">{{ trans('core/base::tables.view_all') }}</a>
                    <div class="small text-white"><x-core::icon name="ti ti-chevron-right" /></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0">{{ format_price($pendingWithdrawals) }}</h4>
                    <p class="mb-0">{{ trans('plugins/affiliate-pro::withdrawal.name') }}</p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('affiliate-pro.withdrawals.index') }}">{{ trans('core/base::tables.view_all') }}</a>
                    <div class="small text-white"><x-core::icon name="ti ti-chevron-right" /></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <h4 class="mb-0">{{ format_price($totalWithdrawn) }}</h4>
                    <p class="mb-0">{{ trans('plugins/affiliate-pro::affiliate.total_withdrawn') }}</p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('affiliate-pro.withdrawals.index') }}">{{ trans('core/base::tables.view_all') }}</a>
                    <div class="small text-white"><x-core::icon name="ti ti-chevron-right" /></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>{{ trans('plugins/affiliate-pro::commission.name') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ trans('core/base::tables.id') }}</th>
                                    <th>{{ trans('plugins/affiliate-pro::commission.affiliate') }}</th>
                                    <th>{{ trans('plugins/affiliate-pro::commission.order') }}</th>
                                    <th>{{ trans('plugins/affiliate-pro::commission.amount') }}</th>
                                    <th>{{ trans('plugins/affiliate-pro::commission.status') }}</th>
                                    <th>{{ trans('plugins/affiliate-pro::commission.created_at') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestCommissions as $commission)
                                    <tr>
                                        <td>{{ $commission->id }}</td>
                                        <td>{{ $commission->affiliate->customer->name }}</td>
                                        <td>#{{ $commission->order_id }}</td>
                                        <td>{{ format_price($commission->amount) }}</td>
                                        <td>{{ $commission->status }}</td>
                                        <td>{{ $commission->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>{{ trans('plugins/affiliate-pro::withdrawal.name') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ trans('core/base::tables.id') }}</th>
                                    <th>{{ trans('plugins/affiliate-pro::withdrawal.affiliate') }}</th>
                                    <th>{{ trans('plugins/affiliate-pro::withdrawal.amount') }}</th>
                                    <th>{{ trans('plugins/affiliate-pro::withdrawal.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestWithdrawals as $withdrawal)
                                    <tr>
                                        <td>{{ $withdrawal->id }}</td>
                                        <td>{{ $withdrawal->affiliate->customer->name }}</td>
                                        <td>{{ format_price($withdrawal->amount) }}</td>
                                        <td>{{ $withdrawal->status }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ trans('plugins/affiliate-pro::affiliate.pending_requests') }}</h4>
        </div>
        <div class="card-body">
            {!! $table->renderTable() !!}
        </div>
    </div>
@stop

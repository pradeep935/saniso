@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="widget meta-boxes">
                <div class="widget-title">
                    <h4>{{ trans('plugins/ecommerce::ecommerce.project_requests') }}</h4>
                    <div class="widget-controls">
                        @if(Auth::user()->hasPermission('project-requests.export'))
                            <a href="{{ route('project-requests.export') }}" class="btn btn-success">
                                <i class="fa fa-download"></i> {{ trans('core/base::forms.export') }}
                            </a>
                        @endif
                    </div>
                </div>
                <div class="widget-body">
                    <div class="table-responsive">
                        {!! $dataTable->table(['class' => 'table table-striped table-hover']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer')
    {!! $dataTable->scripts() !!}
@endpush
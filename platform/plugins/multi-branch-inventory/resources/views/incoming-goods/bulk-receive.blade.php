@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="page-content">
        <div class="page-header">
            <h1>{{ trans('Bulk Receive Goods') }}</h1>
            <div class="page-header-actions">
                <a href="{{ route('incoming-goods.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> {{ trans('Back') }}
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ trans('Receive Multiple Incoming Goods') }}</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('incoming-goods.bulk-process') }}" id="bulkReceiveForm">
                            @csrf

                            <div class="form-group">
                                <label for="branch_id">{{ trans('Branch') }} <span class="text-danger">*</span></label>
                                <select name="branch_id" id="branch_id" class="form-control" required>
                                    <option value="">{{ trans('Select a branch') }}</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>{{ trans('Incoming Goods to Receive') }} <span class="text-danger">*</span></label>
                                <div id="goodsList" class="list-group">
                                    <p class="text-muted">{{ trans('Select a branch to load pending goods') }}</p>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-check"></i> {{ trans('Mark Selected as Received') }}
                                </button>
                                <a href="{{ route('incoming-goods.index') }}" class="btn btn-default">
                                    {{ trans('Cancel') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            // Load pending goods when branch is selected
            $('#branch_id').on('change', function() {
                const branchId = $(this).val();
                
                if (!branchId) {
                    $('#goodsList').html('<p class="text-muted">{{ trans("Select a branch to load pending goods") }}</p>');
                    return;
                }

                $.ajax({
                    url: '{{ route("incoming-goods.get-pending") }}',
                    method: 'GET',
                    data: { branch_id: branchId },
                    success: function(response) {
                        let html = '';
                        
                        if (response.length === 0) {
                            html = '<p class="text-muted">{{ trans("No pending goods for this branch") }}</p>';
                        } else {
                            response.forEach(function(goods, index) {
                                html += `
                                    <div class="list-group-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input bulk-receive-checkbox" 
                                                   id="goods_${goods.id}" name="incoming_goods_ids[]" value="${goods.id}">
                                            <label class="custom-control-label" for="goods_${goods.id}">
                                                <strong>${goods.supplier_name}</strong> - 
                                                <small>${goods.items_count} items</small><br>
                                                <small class="text-muted">${goods.receiving_date}</small>
                                            </label>
                                        </div>
                                    </div>
                                `;
                            });
                        }
                        
                        $('#goodsList').html(html);
                    },
                    error: function() {
                        $('#goodsList').html('<p class="text-danger">{{ trans("Error loading goods") }}</p>');
                    }
                });
            });

            // Form submission
            $('#bulkReceiveForm').on('submit', function(e) {
                const selected = $('input[name="incoming_goods_ids[]"]:checked').length;
                
                if (selected === 0) {
                    e.preventDefault();
                    toastr.error('{{ trans("Please select at least one item") }}');
                    return false;
                }
            });
        });
    </script>
@endsection

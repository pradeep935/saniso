@extends('core/base::layouts.master')

@section('content')
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ __('Duplicate Products Manager') }}</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="GET" action="{{ route('admin.duplicate-products') }}">
                        <div class="input-group">
                            <select name="filter_type" class="form-select">
                                <option value="barcode" {{ $filterType === 'barcode' ? 'selected' : '' }}>Find by Barcode (Priority 1)</option>
                                <option value="title" {{ $filterType === 'title' ? 'selected' : '' }}>Find by Title (Priority 2)</option>
                            </select>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i> Search Duplicates
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <span class="badge bg-info">Found: {{ count($duplicates) }} duplicate groups</span>
                    @if(count($duplicates) > 0)
                        <button class="btn btn-danger ms-2" id="bulk-remove-btn">
                            <i class="fa fa-trash-alt"></i> Bulk Remove All Duplicates
                        </button>
                    @endif
                </div>
            </div>

            @if(count($duplicates) > 0)
                <div class="alert alert-warning">
                    <strong>Bulk Remove Strategy:</strong>
                    <div class="mt-2">
                        <select id="keep-strategy" class="form-select form-select-sm" style="max-width: 300px; display: inline-block;">
                            <option value="first">Keep First Product (Lowest ID)</option>
                            <option value="last">Keep Last Product (Highest ID)</option>
                            <option value="lowest_id">Keep Lowest ID</option>
                        </select>
                        <small class="text-muted ms-2">Choose which product to keep in each duplicate group</small>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ $filterType === 'barcode' ? 'Barcode' : 'Title' }}</th>
                                <th>{{ $filterType === 'barcode' ? 'Sample Name' : 'Barcode' }}</th>
                                <th>Duplicates Count</th>
                                <th>Product IDs</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($duplicates as $duplicate)
                                <tr id="duplicate-row-{{ md5(json_encode($duplicate->product_ids)) }}">
                                    <td>
                                        @if($filterType === 'barcode')
                                            <code>{{ $duplicate->barcode }}</code>
                                        @else
                                            {{ \Illuminate\Support\Str::limit($duplicate->name, 50) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($filterType === 'barcode')
                                            {{ \Illuminate\Support\Str::limit($duplicate->name, 40) }}
                                        @else
                                            <code>{{ $duplicate->barcode }}</code>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-danger">{{ $duplicate->count }} duplicates</span></td>
                                    <td>
                                        <div class="btn-group">
                                            @foreach($duplicate->product_ids as $productId)
                                                <a href="{{ route('products.edit', $productId) }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    ID: {{ $productId }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <div class="duplicate-actions">
                                            <div class="mb-2">
                                                <label class="small">Keep product:</label>
                                                <select class="form-select form-select-sm keep-product-select">
                                                    @foreach($duplicate->product_ids as $productId)
                                                        <option value="{{ $productId }}">ID: {{ $productId }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button 
                                                class="btn btn-sm btn-danger remove-duplicates"
                                                data-product-ids="{{ json_encode($duplicate->product_ids) }}"
                                                data-row-id="duplicate-row-{{ md5(json_encode($duplicate->product_ids)) }}">
                                                <i class="fa fa-trash"></i> Remove Duplicates
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> No duplicate products found! Your catalog is clean.
                </div>
            @endif
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Bulk remove all duplicates
            $('#bulk-remove-btn').on('click', function() {
                const keepStrategy = $('#keep-strategy').val();
                const filterType = '{{ $filterType }}';
                const duplicateCount = {{ count($duplicates) }};
                
                if (!confirm(`Are you sure you want to bulk remove ALL duplicates from ${duplicateCount} groups?\n\nStrategy: ${keepStrategy === 'first' ? 'Keep First (Lowest ID)' : keepStrategy === 'last' ? 'Keep Last (Highest ID)' : 'Keep Lowest ID'}\n\nThis action cannot be undone!`)) {
                    return;
                }
                
                const button = $(this);
                button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                
                $.ajax({
                    url: '{{ route('admin.duplicate-products.bulk-remove') }}',
                    method: 'POST',
                    data: {
                        filter_type: filterType,
                        keep_strategy: keepStrategy,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Botble.showSuccess(response.message);
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            Botble.showError(response.message);
                            button.prop('disabled', false).html('<i class="fa fa-trash-alt"></i> Bulk Remove All Duplicates');
                        }
                    },
                    error: function(xhr) {
                        Botble.showError('Error: ' + (xhr.responseJSON?.message || 'Unknown error'));
                        button.prop('disabled', false).html('<i class="fa fa-trash-alt"></i> Bulk Remove All Duplicates');
                    }
                });
            });
            
            // Individual remove
            $('.remove-duplicates').on('click', function() {
                const button = $(this);
                const productIds = JSON.parse(button.data('product-ids'));
                const rowId = button.data('row-id');
                const keepId = button.closest('.duplicate-actions').find('.keep-product-select').val();
                
                if (!confirm(`Are you sure you want to delete ${productIds.length - 1} duplicate product(s) and keep product ID ${keepId}?`)) {
                    return;
                }
                
                button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Deleting...');
                
                $.ajax({
                    url: '{{ route('admin.duplicate-products.remove') }}',
                    method: 'POST',
                    data: {
                        product_ids: productIds,
                        keep_id: keepId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#' + rowId).fadeOut(300, function() {
                                $(this).remove();
                            });
                            Botble.showSuccess(response.message);
                        } else {
                            Botble.showError(response.message);
                            button.prop('disabled', false).html('<i class="fa fa-trash"></i> Remove Duplicates');
                        }
                    },
                    error: function(xhr) {
                        Botble.showError('Error: ' + (xhr.responseJSON?.message || 'Unknown error'));
                        button.prop('disabled', false).html('<i class="fa fa-trash"></i> Remove Duplicates');
                    }
                });
            });
        });
    </script>
@endsection

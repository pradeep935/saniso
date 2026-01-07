@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header')
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/base.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/inventory-main.css') }}">
@endpush

@section('content')
    <div class="max-width-1200">
        <div class="flexbox-annotated-section">
            <div class="flexbox-annotated-section-annotation">
                <div class="annotated-section-title pd-all-20">
                    <h2>{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.adjust_stock') }}</h2>
                </div>
                <div class="annotated-section-description pd-all-20 p-none-t">
                    <p class="color-note">{{ trans('Adjust stock levels for products across branches') }}</p>
                </div>
            </div>

            <div class="flexbox-annotated-section-content">
                <div class="wrapper-content pd-all-20">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('branch-inventory.adjust-stock', 0) }}" id="adjust-stock-form">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3 @error('branch_id') has-error @enderror">
                                    <label for="branch_id" class="control-label required">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.branch') }}</label>
                                    <select class="form-control" id="branch_id" name="branch_id" required>
                                        <option value="">{{ trans('Select Branch') }}</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }} ({{ $branch->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3 @error('product_id') has-error @enderror">
                                    <label for="product_id" class="control-label required">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.product') }}</label>
                                    <select class="form-control" id="product_id" name="product_id" required>
                                        <option value="">{{ trans('Select Product') }}</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_id')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3 @error('adjustment_type') has-error @enderror">
                                    <label for="adjustment_type" class="control-label required">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.adjustment_type') }}</label>
                                    <select class="form-control" id="adjustment_type" name="adjustment_type" required>
                                        <option value="">{{ trans('Select Type') }}</option>
                                        <option value="add" {{ old('adjustment_type') == 'add' ? 'selected' : '' }}>
                                            {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.add_stock') }}
                                        </option>
                                        <option value="subtract" {{ old('adjustment_type') == 'subtract' ? 'selected' : '' }}>
                                            {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.subtract_stock') }}
                                        </option>
                                        <option value="set" {{ old('adjustment_type') == 'set' ? 'selected' : '' }}>
                                            {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.set_exact_amount') }}
                                        </option>
                                    </select>
                                    @error('adjustment_type')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-3 @error('quantity') has-error @enderror">
                                    <label for="quantity" class="control-label required">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.quantity') }}</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" 
                                           value="{{ old('quantity') }}" min="0" step="1" required>
                                    @error('quantity')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="current_stock" class="control-label">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.current_stock') }}</label>
                                    <input type="text" class="form-control" id="current_stock" readonly placeholder="Select product to view current stock">
                                    <div id="stock_details" class="mt-2 d-none">
                                        <small class="text-muted">
                                            <strong>Ecommerce Total:</strong> <span id="ecommerce_quantity">-</span> units<br>
                                            <strong>Branch Stock:</strong> <span id="branch_quantity">-</span> units
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="reason" class="control-label">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.notes') }}</label>
                                    <textarea class="form-control" id="reason" name="reason" rows="3" 
                                              placeholder="Reason for stock adjustment">{{ old('reason') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-info">
                                <i class="fa fa-save"></i> {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.save') }}
                            </button>
                            <a href="{{ route('branch-inventory.index') }}" class="btn btn-secondary">
                                <i class="fa fa-times"></i> {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#branch_id, #product_id').on('change', function() {
                var branchId = $('#branch_id').val();
                var productId = $('#product_id').val();
                
                if (branchId && productId) {
                    // Fetch current stock level
                    $.ajax({
                        url: '{{ route("branch-inventory.details", ["id" => ":id"]) }}'.replace(':id', productId),
                        method: 'GET',
                        data: { branch_id: branchId },
                        success: function(response) {
                            if (response.success) {
                                var data = response.data;
                                
                                // Update main current stock field
                                $('#current_stock').val(data.branch_quantity + ' units (Branch)');
                                
                                // Update detailed quantities
                                $('#ecommerce_quantity').text(data.ecommerce_quantity);
                                $('#branch_quantity').text(data.branch_quantity);
                                $('#stock_details').show();
                                
                                // Show helpful message if no branch inventory exists
                                if (!data.has_branch_inventory) {
                                    $('#current_stock').val('0 units (Not in branch inventory)');
                                    $('#current_stock').addClass('text-warning');
                                } else {
                                    $('#current_stock').removeClass('text-warning');
                                }
                            } else {
                                $('#current_stock').val('Unable to load stock');
                                $('#stock_details').hide();
                            }
                        },
                        error: function() {
                            $('#current_stock').val('Error loading stock');
                            $('#stock_details').hide();
                        }
                    });
                } else {
                    $('#current_stock').val('');
                    $('#stock_details').hide();
                    $('#ecommerce_quantity').text('-');
                    $('#branch_quantity').text('-');
                }
            });
            
            // Update form action when branch/product selected
            $('#branch_id, #product_id').on('change', function() {
                var branchId = $('#branch_id').val();
                var productId = $('#product_id').val();
                
                if (branchId && productId) {
                    // Find or create inventory item ID for the action URL
                    // For now, we'll handle this in the controller
                    var actionUrl = '{{ route("branch-inventory.adjust-stock", ":id") }}';
                    actionUrl = actionUrl.replace(':id', branchId + '_' + productId);
                    $('#adjust-stock-form').attr('action', actionUrl);
                }
            });
        });
    </script>
@endsection
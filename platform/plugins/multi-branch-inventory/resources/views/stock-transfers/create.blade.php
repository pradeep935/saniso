@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-1200">
        <div class="flexbox-annotated-section">
            <div class="flexbox-annotated-section-annotation">
                <div class="annotated-section-title pd-all-20">
                    <h2>{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.new_transfer') }}</h2>
                </div>
                <div class="annotated-section-description pd-all-20 p-none-t">
                    <p class="color-note">{{ trans('Create a new stock transfer between branches') }}</p>
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

                    <form method="POST" action="{{ route('stock-transfers.store') }}" id="stock-transfer-form">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3 @error('from_branch_id') has-error @enderror">
                                    <label for="from_branch_id" class="control-label required">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.from_branch') }}</label>
                                    <select class="form-control" id="from_branch_id" name="from_branch_id" required>
                                        <option value="">{{ trans('Select Source Branch') }}</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('from_branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }} ({{ $branch->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('from_branch_id')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3 @error('to_branch_id') has-error @enderror">
                                    <label for="to_branch_id" class="control-label required">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.to_branch') }}</label>
                                    <select class="form-control" id="to_branch_id" name="to_branch_id" required>
                                        <option value="">{{ trans('Select Destination Branch') }}</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('to_branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }} ({{ $branch->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('to_branch_id')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="notes" class="control-label">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.notes') }}</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                                              placeholder="Transfer notes and instructions">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <h4>{{ trans('Transfer Items') }}</h4>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <button type="button" class="btn btn-success" id="add-item">
                                                    <i class="fa fa-plus"></i> {{ trans('Add Item') }}
                                                </button>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="transfer-items-table">
                                                <thead>
                                                    <tr>
                                                        <th width="40%">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.product') }}</th>
                                                        <th width="20%">{{ trans('Available Stock') }}</th>
                                                        <th width="20%">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.quantity') }}</th>
                                                        <th width="10%">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.actions') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="items-container">
                                                    <tr id="no-items-row">
                                                        <td colspan="4" class="text-center text-muted">
                                                            {{ trans('No items added yet. Click "Add Item" to start.') }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-info">
                                <i class="fa fa-save"></i> {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.create') }}
                            </button>
                            <a href="{{ route('stock-transfers.index') }}" class="btn btn-secondary">
                                <i class="fa fa-times"></i> {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Item Row Template -->
    <template id="item-row-template">
        <tr class="item-row">
            <td>
                <select class="form-control product-select" name="items[INDEX][product_id]" required>
                    <option value="">{{ trans('Select Product') }}</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <span class="available-stock text-muted">-</span>
            </td>
            <td>
                <input type="number" class="form-control quantity-input" name="items[INDEX][quantity_requested]" 
                       min="1" step="1" required placeholder="0">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-item">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    </template>

    <script>
        $(document).ready(function() {
            let itemIndex = 0;

            // Add new item row
            $('#add-item').click(function() {
                let template = $('#item-row-template').html();
                template = template.replace(/INDEX/g, itemIndex);
                
                $('#no-items-row').hide();
                $('#items-container').append(template);
                itemIndex++;
            });

            // Remove item row
            $(document).on('click', '.remove-item', function() {
                $(this).closest('tr').remove();
                
                if ($('.item-row').length === 0) {
                    $('#no-items-row').show();
                }
            });

            // Get available stock when product is selected
            $(document).on('change', '.product-select', function() {
                let productId = $(this).val();
                let branchId = $('#from_branch_id').val();
                let stockSpan = $(this).closest('tr').find('.available-stock');
                let quantityInput = $(this).closest('tr').find('.quantity-input');

                if (productId && branchId) {
                    $.ajax({
                        url: '{{ route("stock-transfers.get-branch-products") }}',
                        method: 'GET',
                        data: {
                            branch_id: branchId,
                            product_id: productId
                        },
                        success: function(response) {
                            if (response.success && response.data) {
                                let availableStock = response.data.quantity_available || 0;
                                stockSpan.text(availableStock + ' units');
                                quantityInput.attr('max', availableStock);
                            } else {
                                stockSpan.text('0 units');
                                quantityInput.attr('max', 0);
                            }
                        },
                        error: function() {
                            stockSpan.text('Unknown');
                            quantityInput.removeAttr('max');
                        }
                    });
                } else {
                    stockSpan.text('-');
                    quantityInput.removeAttr('max');
                }
            });

            // Update stock when source branch changes
            $('#from_branch_id').change(function() {
                $('.product-select').trigger('change');
            });

            // Form validation
            $('#stock-transfer-form').submit(function(e) {
                let hasItems = $('.item-row').length > 0;
                
                if (!hasItems) {
                    e.preventDefault();
                    alert('Please add at least one item to transfer.');
                    return false;
                }

                let fromBranch = $('#from_branch_id').val();
                let toBranch = $('#to_branch_id').val();

                if (fromBranch === toBranch) {
                    e.preventDefault();
                    alert('Source and destination branches cannot be the same.');
                    return false;
                }

                return true;
            });
        });
    </script>
@endsection
@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<style>
    .page-content {
        background: #f5f7fa;
    }
    
    .modern-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px 0;
        margin-bottom: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .modern-header h1 {
        margin: 0;
        font-size: 32px;
        font-weight: 600;
    }
    
    .form-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 25px;
        border: none;
    }
    
    .form-card .card-header {
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        padding: 20px;
        border-radius: 8px 8px 0 0;
    }
    
    .form-card .card-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-card .card-header i {
        color: #667eea;
        font-size: 18px;
    }
    
    .form-card .card-body {
        padding: 25px;
    }
    
    .form-group label {
        font-weight: 500;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .form-group label.required::after {
        content: ' *';
        color: #e74c3c;
    }
    
    .form-control {
        border: 1px solid #d4d9e3;
        border-radius: 6px;
        padding: 10px 12px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .items-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .items-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .add-item-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    
    .add-item-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    .items-table-wrapper {
        overflow-x: auto;
        border-radius: 6px;
        border: 1px solid #e9ecef;
    }
    
    .items-table {
        background: white;
        margin: 0;
    }
    
    .items-table thead {
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
    }
    
    .items-table th {
        padding: 15px;
        font-weight: 600;
        color: #2c3e50;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .items-table td {
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }
    
    .items-table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .items-table input[type="text"],
    .items-table input[type="number"],
    .items-table select {
        border: 1px solid #d4d9e3;
        border-radius: 4px;
        padding: 8px 10px;
        font-size: 13px;
        width: 100%;
    }
    
    .items-table input[type="text"]:focus,
    .items-table input[type="number"]:focus,
    .items-table select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
    }
    
    .total-cost {
        font-weight: 600;
        color: #667eea;
        font-size: 14px;
    }
    
    .remove-item-btn {
        background: #e74c3c;
        border: none;
        color: white;
        padding: 6px 10px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.2s ease;
    }
    
    .remove-item-btn:hover {
        background: #c0392b;
        transform: scale(1.05);
    }
    
    .summary-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 6px;
        margin-top: 20px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    
    .summary-row:last-child {
        border-bottom: none;
        font-size: 18px;
        font-weight: 600;
    }
    
    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 30px;
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 12px 30px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 15px;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        color: white;
        text-decoration: none;
    }
    
    .btn-cancel {
        background: #ecf0f1;
        border: 1px solid #d4d9e3;
        color: #2c3e50;
        padding: 12px 30px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .btn-cancel:hover {
        background: #e0e6ed;
        color: #2c3e50;
    }
    
    .no-items-message {
        text-align: center;
        padding: 40px 20px;
        color: #95a5a6;
        background: #f8f9fa;
        border-radius: 6px;
        font-style: italic;
    }
    
    .alert {
        border-radius: 6px;
        border: none;
        margin-bottom: 20px;
    }
    
    .alert-danger {
        background: #fee;
        color: #c0392b;
        padding: 15px 20px;
    }
</style>

<div class="page-content">
    <div class="modern-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <h1><i class="fa fa-edit"></i> {{ trans('Edit Incoming Goods') }}</h1>
                </div>
                <div class="col-md-3 text-right">
                    <a href="{{ route('incoming-goods.index') }}" class="btn btn-light">
                        <i class="fa fa-arrow-left"></i> {{ trans('Back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5 style="margin-top: 0;">{{ trans('Please correct the following errors:') }}</h5>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('incoming-goods.update', $incomingGood->id) }}" id="incoming-form">
            @csrf
            @method('PUT')

            <!-- Delivery Information Card -->
            <div class="form-card">
                <div class="card-header">
                    <h5><i class="fa fa-truck"></i> {{ trans('Delivery Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required">{{ trans('Branch') }}</label>
                                <select name="branch_id" id="branch_id" class="form-control" required>
                                    <option value="">-- {{ trans('Select Branch') }} --</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $incomingGood->branch_id == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required">{{ trans('Supplier Name') }}</label>
                                <input type="text" name="supplier_name" class="form-control" 
                                       value="{{ old('supplier_name', $incomingGood->supplier_name) }}" 
                                       placeholder="e.g., ABC Wholesale Ltd." required>
                                @error('supplier_name')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required">{{ trans('Receiving Date') }}</label>
                                <input type="date" name="receiving_date" class="form-control" 
                                       value="{{ old('receiving_date', $incomingGood->receiving_date->format('Y-m-d')) }}" required>
                                @error('receiving_date')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ trans('Reference/PO Number') }}</label>
                                <input type="text" name="reference_number" class="form-control" 
                                       value="{{ old('reference_number', $incomingGood->reference_number) }}" 
                                       placeholder="e.g., PO-2025-001">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ trans('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2" 
                                  placeholder="Any additional notes about this delivery...">{{ old('notes', $incomingGood->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Items Card -->
            <div class="form-card">
                <div class="card-header">
                    <div class="items-header">
                        <h5><i class="fa fa-list"></i> {{ trans('Items') }}</h5>
                        <button type="button" class="add-item-btn" id="add-item-btn">
                            <i class="fa fa-plus"></i> {{ trans('Add Item') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="items-table-wrapper">
                        <table class="items-table" id="items-table">
                            <thead>
                                <tr>
                                    <th style="width: 35%;">{{ trans('Product') }}</th>
                                    <th style="width: 15%;">{{ trans('Expected Qty') }}</th>
                                    <th style="width: 15%;">{{ trans('Received Qty') }}</th>
                                    <th style="width: 15%;">{{ trans('Unit Cost') }}</th>
                                    <th style="width: 12%;">{{ trans('Total') }}</th>
                                    <th style="width: 8%; text-align: center;">{{ trans('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="items-container">
                                @forelse($incomingGood->items as $index => $item)
                                    <tr class="item-row">
                                        <td>
                                            <select class="form-control product-select" name="items[{{ $index }}][product_id]" required>
                                                <option value="">-- {{ trans('Select Product') }} --</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" 
                                                        {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }} (SKU: {{ $product->sku ?: 'N/A' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" class="product-name" name="items[{{ $index }}][product_name]" value="{{ $item->product_name }}">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control expected-qty" name="items[{{ $index }}][expected_quantity]" 
                                                   min="1" step="1" required value="{{ $item->quantity_expected ?? 0 }}">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control received-qty" name="items[{{ $index }}][received_quantity]" 
                                                   min="0" step="1" value="{{ $item->quantity_received ?? 0 }}">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control unit-cost" name="items[{{ $index }}][unit_cost]" 
                                                   min="0" step="0.01" required value="{{ $item->unit_cost ?? 0 }}">
                                        </td>
                                        <td style="text-align: right;">
                                            <span class="total-cost">${{ number_format(($item->quantity_expected ?? 0) * ($item->unit_cost ?? 0), 2) }}</span>
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" class="remove-item-btn">
                                                <i class="fa fa-trash"></i> {{ trans('Remove') }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="no-items-row">
                                        <td colspan="6" class="no-items-message">
                                            {{ trans('No items added yet. Click "Add Item" to start adding products.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary -->
                    <div class="summary-box">
                        <div class="summary-row">
                            <span class="summary-label">{{ trans('Total Items') }}:</span>
                            <span class="summary-value"><span id="total-items">{{ $incomingGood->items->count() }}</span> items</span>
                        </div>
                        <!-- Total Value removed — only total items are shown -->
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fa fa-save"></i> {{ trans('Save Changes') }}
                </button>
                <a href="{{ route('incoming-goods.index') }}" class="btn-cancel">
                    <i class="fa fa-times"></i> {{ trans('Cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Item Row Template -->
<template id="item-row-template">
    <tr class="item-row">
        <td>
            <select class="form-control product-select" name="items[INDEX][product_id]" required>
                <option value="">-- {{ trans('Select Product') }} --</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                        {{ $product->name }} (SKU: {{ $product->sku ?: 'N/A' }})
                    </option>
                @endforeach
            </select>
            <input type="hidden" class="product-name" name="items[INDEX][product_name]" value="">
        </td>
        <td>
            <input type="number" class="form-control expected-qty" name="items[INDEX][expected_quantity]" 
                   min="1" step="1" required value="0">
        </td>
        <td>
            <input type="number" class="form-control received-qty" name="items[INDEX][received_quantity]" 
                   min="0" step="1" value="0">
        </td>
        <td>
            <input type="number" class="form-control unit-cost" name="items[INDEX][unit_cost]" 
                   min="0" step="0.01" required value="0">
        </td>
        <td style="text-align: right;">
            <span class="total-cost">$0.00</span>
        </td>
        <td style="text-align: center;">
            <button type="button" class="remove-item-btn">
                <i class="fa fa-trash"></i> {{ trans('Remove') }}
            </button>
        </td>
    </tr>
</template>

@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            const products = @json($products);
            let itemCount = {{ $incomingGood->items->count() }};

            // Add new item row
            $(document).on('click', '#add-item-btn', function(e) {
                e.preventDefault();
                let template = $('#item-row-template').html();
                template = template.replace(/INDEX/g, itemCount);
                
                $('#no-items-row').hide();
                $('#items-container').append(template);
                itemCount++;
                updateTotals();
            });

            // Product selection - auto-fill price
            $(document).on('change', '.product-select', function() {
                let selectedOption = $(this).find(':selected');
                let productId = selectedOption.val();
                let productName = selectedOption.text();
                let price = selectedOption.data('price') || 0;

                let row = $(this).closest('tr');
                row.find('.unit-cost').val(price);
                row.find('.product-name').val(productName);
                
                updateTotals();
            });

            // Remove item row
            $(document).on('click', '.remove-item-btn', function(e) {
                e.preventDefault();
                $(this).closest('tr').remove();
                
                if ($('.item-row').length === 0) {
                    $('#no-items-row').show();
                }
                updateTotals();
            });

            // Calculate totals when quantities or costs change
            $(document).on('input', '.expected-qty, .unit-cost', function() {
                let row = $(this).closest('tr');
                let expectedQty = parseFloat(row.find('.expected-qty').val()) || 0;
                let unitCost = parseFloat(row.find('.unit-cost').val()) || 0;
                let totalCost = expectedQty * unitCost;
                
                row.find('.total-cost').text('$' + totalCost.toFixed(2));
                updateTotals();
            });

            // Auto-fill received quantity
            $(document).on('input', '.expected-qty', function() {
                let expectedQty = $(this).val();
                let receivedQtyInput = $(this).closest('tr').find('.received-qty');
                
                if (receivedQtyInput.val() == 0 || receivedQtyInput.val() == '') {
                    receivedQtyInput.val(expectedQty);
                }
            });

            function updateTotals() {
                let totalItems = $('.item-row').length;
                let totalCost = 0;
                
                $('.item-row').each(function() {
                    let expectedQty = parseFloat($(this).find('.expected-qty').val()) || 0;
                    let unitCost = parseFloat($(this).find('.unit-cost').val()) || 0;
                    totalCost += expectedQty * unitCost;
                });
                
                $('#total-items').text(totalItems);
                $('#total-cost').text(totalCost.toFixed(2));
            }

            // Form validation
            $('#incoming-form').submit(function(e) {
                let hasItems = $('.item-row').length > 0;
                
                if (!hasItems) {
                    e.preventDefault();
                    alert('{{ trans('Please add at least one item to receive.') }}');
                    return false;
                }

                return true;
            });

            // Initialize totals on page load
            updateTotals();
        });
    </script>
@endsection

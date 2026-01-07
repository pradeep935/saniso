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
    
    .summary-label {
        font-weight: 500;
    }
    
    .summary-value {
        text-align: right;
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
    
    .temp-product-row {
        background: #fff9e6 !important;
    }
    
    .temp-product-row:hover {
        background: #fff4d6 !important;
    }
    
    #barcode-input {
        font-size: 16px;
        font-weight: 500;
        border: 2px solid #667eea;
        padding: 12px 15px;
    }
    
    #barcode-input:focus {
        border-color: #764ba2;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
    }
    
    .badge-warning {
        background: #f39c12;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .badge-primary {
        background: #667eea;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .product-name-input {
        font-weight: 500;
    }
    
    #camera-scanner-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 99999;
        justify-content: center;
        align-items: center;
    }
    
    #camera-scanner-modal.active {
        display: flex;
    }
    
    .scanner-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        max-width: 640px;
        width: 90%;
        position: relative;
    }
    
    .scanner-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .scanner-header h3 {
        margin: 0;
        color: #2c3e50;
        font-size: 20px;
    }
    
    .scanner-close-btn {
        background: #e74c3c;
        border: none;
        color: white;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .scanner-close-btn:hover {
        background: #c0392b;
    }
    
    #interactive {
        width: 100%;
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        background: #000;
    }
    
    #interactive video {
        width: 100%;
        height: auto;
        border-radius: 8px;
        background: #000;
    }
    
    #interactive canvas {
        position: absolute;
        top: 0;
        left: 0;
    }
    
    .scanner-status {
        margin-top: 15px;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 6px;
        text-align: center;
        font-weight: 500;
        color: #2c3e50;
    }
    
    .scanner-status.scanning {
        background: #d4edda;
        color: #155724;
    }
    
    .scanner-status.detected {
        background: #cce5ff;
        color: #004085;
    }
    
    .camera-btn {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: transform 0.2s ease;
        margin-left: 10px;
    }
    
    .camera-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
    }
    
    .drawingBuffer {
        position: absolute;
        top: 0;
        left: 0;
    }
</style>

<div class="page-content">
    <div class="modern-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <h1><i class="fa fa-inbox"></i> {{ trans('Receive New Goods') }}</h1>
                </div>
                <div class="col-md-3 text-right">
                        <a href="{{ route('incoming-goods.index') }}" class="btn btn-light">
                            <i class="fa fa-arrow-left"></i> {{ trans('Back') }}
                        </a>
                        <a href="{{ route('temporary-products.index') }}" class="btn btn-warning" style="margin-left:8px;">
                            <i class="fa fa-box-open"></i> {{ trans('New Goods') }}
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

        <form method="POST" action="{{ route('incoming-goods.store') }}" id="incoming-form" enctype="multipart/form-data">
            @csrf

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
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
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
                            <div class="form-group" style="position:relative;">
                                <label class="required">{{ trans('Supplier Name') }}</label>
                                <input type="text" name="supplier_name" id="supplier_name" class="form-control" 
                                       value="{{ old('supplier_name') }}" 
                                       placeholder="e.g., ABC Wholesale Ltd." required autocomplete="off">
                                <div id="supplier_suggestions" class="list-group mt-1 d-none" style="position: absolute; z-index: 9999; max-height: 200px; overflow:auto; width:100%;"></div>
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
                                       value="{{ old('receiving_date', date('Y-m-d')) }}" required>
                                @error('receiving_date')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ trans('Reference/PO Number') }}</label>
                                <input type="text" name="reference_number" class="form-control" 
                                       value="{{ old('reference_number') }}" 
                                       placeholder="e.g., PO-2025-001">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ trans('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2" 
                                  placeholder="Any additional notes about this delivery...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ trans('CMR (truck document)') }}</label>
                                <input type="file" name="cmr_images[]" accept="image/*" multiple class="form-control">
                                <small class="form-text text-muted">Upload CMR / transport document image</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ trans('Packing Slip') }}</label>
                                <input type="file" name="packing_slip_images[]" accept="image/*" multiple class="form-control">
                                <small class="form-text text-muted">Upload packing slip image</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ trans('Delivery / Product Image') }}</label>
                                <input type="file" name="delivery_images[]" accept="image/*" multiple class="form-control">
                                <small class="text-muted">You can upload up to 5 images per field.</small>

                                <div class="mt-3">
                                    <label class="form-label">Proforma / Invoice</label>
                                    <input type="file" name="proforma_images[]" accept="image/*,application/pdf" multiple class="form-control">
                                </div>
                                <small class="form-text text-muted">Photo of how product was delivered</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ trans('Box Barcode') }}</label>
                                <input type="text" name="box_barcode" class="form-control" value="{{ old('box_barcode') }}" placeholder="Scan or enter box barcode">
                                <small class="form-text text-muted">Optional: box-level barcode</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barcode Scanner Card -->
            <div class="form-card">
                <div class="card-header">
                    <h5><i class="fa fa-barcode"></i> {{ trans('Barcode Scanner') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group mb-0">
                                <label>{{ trans('Scan or Enter Barcode/SKU') }}</label>
                                <input type="text" id="barcode-input" class="form-control" 
                                       placeholder="Scan barcode or type SKU and press Enter..." 
                                       autocomplete="off">
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle"></i> Scan product barcode or enter SKU to automatically add to list
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <div style="display: flex; gap: 5px;">
                                <button type="button" class="btn add-item-btn" id="scan-btn" style="margin-top: 0; flex: 1;">
                                    <i class="fa fa-search"></i> {{ trans('Lookup') }}
                                </button>
                                <button type="button" class="btn camera-btn" id="camera-btn" style="margin-top: 0; margin-left: 0;" title="Scan with Camera">
                                    <i class="fa fa-camera"></i>
                                </button>
                            </div>
                        <!-- Camera Scanner Modal -->
                        <div id="camera-scanner-modal">
                            <div class="scanner-container">
                                <div class="scanner-header">
                                    <h3><i class="fa fa-camera"></i> {{ trans('Scan Barcode') }}</h3>
                                    <button type="button" class="scanner-close-btn" id="close-scanner">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                                <div id="interactive" class="viewport">
                                    <video id="scanner-video" playsinline muted></video>
                                </div>
                                <div class="scanner-status" id="scanner-status">
                                    <i class="fa fa-info-circle"></i> {{ trans('Position barcode in the camera view') }}
                                </div>
                            </div>
                        </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Card -->
            <div class="form-card">
                <div class="card-header">
                    <div class="items-header">
                        <h5><i class="fa fa-list"></i> {{ trans('Items') }}</h5>
                        <button type="button" class="add-item-btn" id="add-item-btn">
                            <i class="fa fa-plus"></i> {{ trans('Add Item Manually') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="items-table-wrapper">
                        <table class="items-table" id="items-table">
                            <thead>
                                <tr>
                                    <th style="width: 28%;">{{ trans('Product') }}</th>
                                    <th style="width: 8%;">{{ trans('SKU') }}</th>
                                        <th style="width: 8%">{{ trans('EAN/Barcode') }}</th>
                                        <th style="width: 12%">{{ trans('Expected Qty') }}</th>
                                        <th style="width: 12%">{{ trans('Received Qty') }}</th>
                                    <th style="width: 6%;">{{ trans('Type') }}</th>
                                    <th style="width: 6%; text-align: center;">{{ trans('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="items-container">
                                <tr id="no-items-row">
                                    <td colspan="8" class="no-items-message">
                                        {{ trans('No items added yet. Scan barcode or click "Add Item Manually" to start adding products.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary -->
                    <div class="summary-box">
                        <div class="summary-row">
                            <span class="summary-label">{{ trans('Total Items') }}:</span>
                            <span class="summary-value"><span id="total-items">0</span> items</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fa fa-save"></i> {{ trans('Save & Process') }}
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
            <input type="text" class="form-control product-search" placeholder="Search by name, SKU, or barcode..." autocomplete="off">
            <input type="hidden" class="product-id" name="items[INDEX][product_id]" required>
            <input type="hidden" class="product-name" name="items[INDEX][product_name]" required>
            <input type="hidden" class="is-new-product" name="items[INDEX][is_new_product]" value="0">
            <div class="search-results" style="display: none; position: absolute; z-index: 1000; background: white; border: 1px solid #ddd; max-height: 200px; overflow-y: auto; width: 300px;"></div>
        </td>
        <td>
            <input type="text" class="form-control sku-input" name="items[INDEX][sku]" readonly style="background: #f5f5f5;" placeholder="SKU">
        </td>
        <td>
            <input type="text" class="form-control ean-input" name="items[INDEX][ean]" readonly style="background: #f5f5f5;" placeholder="EAN">
        </td>
        <td>
            <input type="number" class="form-control expected-qty" name="items[INDEX][quantity_received]" 
                   min="1" step="1" required value="1" placeholder="1">
        </td>
        <td>
            <input type="number" class="form-control received-qty" name="items[INDEX][received_qty]" 
                   min="0" step="1" value="1" placeholder="1">
        </td>
        <!-- cost fields removed: unit cost and per-line total suppressed -->
        <td style="text-align: center;">
            <span class="badge badge-primary product-type-badge">Regular</span>
        </td>
        <td style="text-align: center;">
            <button type="button" class="remove-item-btn">
                <i class="fa fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<!-- Temporary Product Row Template -->
<template id="temp-item-row-template">
    <tr class="item-row temp-product-row" style="background: #fff9e6;">
        <td>
            <input type="text" class="form-control product-name-input" name="items[INDEX][product_name]" 
                   placeholder="Enter product name..." required>
            <input type="hidden" class="product-select" name="items[INDEX][product_id]" value="">
            <input type="hidden" class="is-new-product" name="items[INDEX][is_new_product]" value="1">
        </td>
        <td>
            <input type="text" class="form-control sku-input" name="items[INDEX][sku]" 
                   placeholder="SKU" readonly style="background: #f5f5f5;">
        </td>
        <td>
            <input type="text" class="form-control ean-input" name="items[INDEX][ean]" 
                   placeholder="EAN/Barcode" readonly style="background: #f5f5f5;">
        </td>
        <td style="width:120px;">
            <button type="button" class="btn btn-sm btn-outline-secondary generate-sku-btn" title="Generate SKU" style="margin-top:6px;">
                <i class="fa fa-cog"></i> SKU
            </button>
        </td>
        <td>
            <input type="number" class="form-control expected-qty" name="items[INDEX][quantity_received]" 
                   min="1" step="1" required value="1" placeholder="1">
        </td>
        <td>
            <input type="number" class="form-control received-qty" name="items[INDEX][received_qty]" 
                   min="0" step="1" value="1" placeholder="1">
        </td>
        <!-- cost fields removed for temporary products -->
        <td style="text-align: center;">
            <span class="badge badge-warning product-type-badge">New</span>
        </td>
        <td style="text-align: center;">
            <button type="button" class="remove-item-btn">
                <i class="fa fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const supplierInput = document.getElementById('supplier_name');
    const suggestions = document.getElementById('supplier_suggestions');

    if (supplierInput) {
        let timeout = null;
        supplierInput.addEventListener('input', function () {
            clearTimeout(timeout);
            const q = this.value.trim();
            if (!q) { suggestions.classList.add('d-none'); return; }
            timeout = setTimeout(() => {
                fetch("{{ route('incoming-goods.api-suppliers') }}?q=" + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        suggestions.innerHTML = '';
                        if (!data || data.length === 0) { suggestions.classList.add('d-none'); return; }
                        data.forEach(s => {
                            const a = document.createElement('a');
                            a.className = 'list-group-item list-group-item-action';
                            a.href = '#';
                            a.textContent = s;
                            a.addEventListener('click', function (ev) {
                                ev.preventDefault();
                                supplierInput.value = s;
                                suggestions.classList.add('d-none');
                            });
                            suggestions.appendChild(a);
                        });
                        suggestions.classList.remove('d-none');
                    });
            }, 250);
        });

        document.addEventListener('click', function (ev) {
            if (!supplierInput.contains(ev.target) && !suggestions.contains(ev.target)) {
                suggestions.classList.add('d-none');
            }
        });
    }
});
</script>

@endsection

@section('javascript')
        <!-- ZXing Library for Barcode Scanning -->
        <script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.20.0/umd/index.min.js"></script>
    
    <script>
        $(document).ready(function() {
            let itemCount = 0;
            let barcodeTimeout;
            let isScanning = false;
            let lastScannedCode = '';
            let lastScanTime = 0;
            let hardwareBuffer = '';
            let hardwareTimer = null;
            let zxingReader = null;
            
            // AJAX Product Search
            $(document).on('input', '.product-search', function() {
                const input = $(this);
                const query = input.val();
                const resultsDiv = input.siblings('.search-results');
                
                if (query.length < 2) {
                    resultsDiv.hide();
                    return;
                }
                
                $.ajax({
                    url: '{{ route("incoming-goods.search-products") }}',
                    method: 'GET',
                    data: { q: query },
                    success: function(products) {
                        if (products.length === 0) {
                            resultsDiv.html('<div style="padding: 10px;">No products found</div>').show();
                            return;
                        }
                        
                        let html = '';
                        products.forEach(function(product) {
                            html += `<div class="search-result-item" style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee;" 
                                         data-id="${product.id}" 
                                         data-name="${product.name}" 
                                         data-sku="${product.sku || ''}" 
                                         data-barcode="${product.barcode || ''}" 
                                         data-price="${product.price}">
                                        <strong>${product.name}</strong><br>
                                        <small>SKU: ${product.sku || 'N/A'} | Price: $${product.price}</small>
                                    </div>`;
                        });
                        resultsDiv.html(html).show();
                    }
                });
            });
            
            // Select product from search results
            $(document).on('click', '.search-result-item', function() {
                const item = $(this);
                const row = item.closest('tr');
                
                row.find('.product-id').val(item.data('id'));
                row.find('.product-name').val(item.data('name'));
                row.find('.product-search').val(item.data('name'));
                row.find('.sku-input').val(item.data('sku'));
                row.find('.ean-input').val(item.data('barcode'));

                item.parent('.search-results').hide();

                updateTotals();
            });
            
            // Hide search results when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).hasClass('product-search') && !$(e.target).closest('.search-results').length) {
                    $('.search-results').hide();
                }
            });

            // Focus barcode input on page load
            $('#barcode-input').focus();

            // Camera Scanner (ZXing) + Hardware Scanner buffer
            $('#camera-btn').on('click', function() {
                startCameraScanner();
            });

            $('#close-scanner').on('click', function() {
                stopCameraScanner();
            });

            async function startCameraScanner() {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    alert('{{ trans('Camera not supported in this browser. Try a hardware scanner.') }}');
                    return;
                }

                $('#camera-scanner-modal').addClass('active');
                $('#scanner-status').html('<i class="fa fa-spinner fa-spin"></i> {{ trans('Starting camera...') }}');

                try {
                    // Prefer back camera on mobile
                    const constraints = {
                        audio: false,
                        video: { facingMode: { ideal: 'environment' }, width: { ideal: 640 }, height: { ideal: 480 } }
                    };
                    const stream = await navigator.mediaDevices.getUserMedia(constraints);
                    const videoElem = document.getElementById('scanner-video');
                    videoElem.srcObject = stream;

                    // Wait for metadata so video dimensions are available
                    await new Promise((resolve, reject) => {
                        const onMeta = () => {
                            videoElem.removeEventListener('loadedmetadata', onMeta);
                            resolve();
                        };
                        const onErr = (e) => {
                            videoElem.removeEventListener('error', onErr);
                            reject(e);
                        };
                        videoElem.addEventListener('loadedmetadata', onMeta);
                        videoElem.addEventListener('error', onErr);
                        // fallback timeout
                        setTimeout(resolve, 1500);
                    });

                    try { await videoElem.play(); } catch (e) { console.debug('video.play() failed', e); }

                    if (!zxingReader) {
                        // Add hints for better detection performance
                        const hints = new Map();
                        try { if (ZXing?.DecodeHintType?.TRY_HARDER) hints.set(ZXing.DecodeHintType.TRY_HARDER, true); } catch(e){}
                        try {
                            if (ZXing?.DecodeHintType?.POSSIBLE_FORMATS && ZXing?.BarcodeFormat) {
                                hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, [
                                    ZXing.BarcodeFormat.EAN_13,
                                    ZXing.BarcodeFormat.EAN_8,
                                    ZXing.BarcodeFormat.UPC_A,
                                    ZXing.BarcodeFormat.CODE_128,
                                    ZXing.BarcodeFormat.ITF
                                ]);
                            }
                        } catch(e){}
                        try { zxingReader = new ZXing.BrowserMultiFormatReader(hints); } catch(e) { zxingReader = new ZXing.BrowserMultiFormatReader(); }
                    }

                    isScanning = true;
                    $('#scanner-status').html('<i class="fa fa-camera"></i> {{ trans('Scanning... Position barcode in view') }}').addClass('scanning');

                    // Pass actual video element rather than id string for robustness
                    try {
                        zxingReader.decodeFromVideoDevice(null, videoElem, (result, err) => {
                            if (!isScanning) return;
                            if (err) {
                                // Not an immediate fatal error; log for diagnostics
                                console.debug('decode error', err);
                                return;
                            }
                            if (result) {
                                const code = result.text || (result.getText ? result.getText() : '');
                                const now = Date.now();
                                if (code === lastScannedCode && (now - lastScanTime) < 1500) {
                                    return;
                                }
                                lastScannedCode = code;
                                lastScanTime = now;

                                $('#scanner-status').html('<i class="fa fa-check"></i> {{ trans('Detected:') }} ' + code).addClass('detected');
                                playBeep();
                                $('#barcode-input').val(code);
                                detectAndAddProduct();

                                setTimeout(() => {
                                    stopCameraScanner();
                                }, 800);
                            }
                        });
                    } catch (decodeErr) {
                        console.error('ZXing decodeFromVideoDevice error', decodeErr);
                        $('#scanner-status').html('<i class="fa fa-exclamation-triangle"></i> {{ trans('Scanner error') }}');
                        setTimeout(() => stopCameraScanner(), 2000);
                    }
                } catch (err) {
                    console.error('startCameraScanner error', err);
                    $('#scanner-status').html('<i class="fa fa-exclamation-triangle"></i> {{ trans('Camera access denied or not available') }}');
                    // show helpful alert for permission or notfound
                    if (err && err.name) {
                        if (err.name === 'NotAllowedError') alert('{{ trans('Camera permission denied. Please enable camera access and try again.') }}');
                        else if (err.name === 'NotFoundError') alert('{{ trans('No camera found. Connect a camera or use a hardware scanner.') }}');
                        else alert('{{ trans('Camera error:') }} ' + (err.message || err.name));
                    }
                    setTimeout(() => stopCameraScanner(), 2000);
                }
            }

            function stopCameraScanner() {
                if (zxingReader) {
                    try { zxingReader.reset(); } catch (e) {}
                }
                const videoElem = document.getElementById('scanner-video');
                if (videoElem && videoElem.srcObject) {
                    videoElem.srcObject.getTracks().forEach(t => t.stop());
                    videoElem.srcObject = null;
                }
                isScanning = false;
                $('#camera-scanner-modal').removeClass('active');
                $('#scanner-status').html('<i class="fa fa-info-circle"></i> {{ trans('Position barcode in the camera view') }}')
                    .removeClass('scanning detected');
                $('#barcode-input').focus();
            }

            function playBeep() {
                try {
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    oscillator.frequency.value = 800;
                    oscillator.type = 'sine';
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.1);
                } catch (e) {
                    // audio not supported
                }
            }

            // Barcode scanning - Enter key or button click
            $('#barcode-input').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    detectAndAddProduct();
                }
            });

            $('#scan-btn').on('click', function(e) {
                e.preventDefault();
                detectAndAddProduct();
            });

            // Function to detect product by barcode and add to list
            function detectAndAddProduct() {
                let code = $('#barcode-input').val().trim();
                
                if (!code) {
                    alert('{{ trans('Please enter a barcode or SKU') }}');
                    return;
                }

                // Show loading state
                $('#scan-btn').html('<i class="fa fa-spinner fa-spin"></i> {{ trans('Checking...') }}').prop('disabled', true);

                // Call API to lookup product by barcode/code
                $.ajax({
                    url: '{{ route('incoming-goods.get-product-by-code') }}',
                    type: 'GET',
                    data: { code: code },
                    success: function(response) {
                        if (response.found && response.product) {
                            // ✅ Product found - add to list with full details
                            addProductToList(response.product, code);
                            showNotification('success', '✓ {{ trans('Product found and added!') }}');
                        } else {
                            // ❌ Product NOT found - show "not available" message
                            showNotification('warning', '⚠ {{ trans('Product not available in catalog') }}');
                            
                            // Add as temporary incoming goods
                            addTemporaryProduct(code);
                        }
                        
                        // Clear input and refocus
                        $('#barcode-input').val('').focus();
                    },
                    error: function() {
                        showNotification('warning', '⚠ {{ trans('Error checking product. Added as temporary.') }}');
                        addTemporaryProduct(code);
                        $('#barcode-input').val('').focus();
                    },
                    complete: function() {
                        // Reset button
                        $('#scan-btn').html('<i class="fa fa-search"></i> {{ trans('Lookup') }}').prop('disabled', false);
                    }
                });
            }

            // Add existing product to list
            function addProductToList(product, code) {
                // Check if product already in list
                let existingRow = null;
                $('.item-row').each(function() {
                    let productId = $(this).find('.product-id').val();
                    if (productId == product.id) {
                        existingRow = $(this);
                        return false; // break loop
                    }
                });

                if (existingRow) {
                    // Product already exists - increment quantity
                    let qtyInput = existingRow.find('.expected-qty');
                    let currentQty = parseInt(qtyInput.val()) || 0;
                    qtyInput.val(currentQty + 1).trigger('input');
                    
                    // Highlight the row briefly
                    existingRow.css('background', '#d4edda');
                    setTimeout(() => existingRow.css('background', ''), 1000);
                } else {
                    // Add new row
                    let template = $('#item-row-template').html();
                    template = template.replace(/INDEX/g, itemCount);
                    
                    $('#no-items-row').hide();
                    $('#items-container').append(template);
                    
                    let newRow = $('#items-container .item-row').last();
                    
                    // Set product details
                    newRow.find('.product-id').val(product.id);
                    newRow.find('.product-name').val(product.name);
                    newRow.find('.product-search').val(product.name);
                    newRow.find('.sku-input').val(product.sku || product.barcode || code);
                    newRow.find('.ean-input').val(product.barcode || code);
                    newRow.find('.expected-qty').val(1);
                    newRow.find('.received-qty').val(1);

                    itemCount++;
                    updateTotals();
                }
            }

            // Add temporary/new product to list
            function addTemporaryProduct(code) {
                let template = $('#temp-item-row-template').html();
                template = template.replace(/INDEX/g, itemCount);
                
                $('#no-items-row').hide();
                $('#items-container').append(template);
                
                let newRow = $('#items-container .item-row').last();
                
                // Set barcode/SKU
                newRow.find('.sku-input').val(code);
                newRow.find('.ean-input').val(code);
                newRow.find('.expected-qty').val(1);
                newRow.find('.received-qty').val(1);
                // If SKU empty, generate one client-side preview (will also be generated server-side if missing)
                if (!newRow.find('.sku-input').val()) {
                    newRow.find('.sku-input').val(generateClientSku(newRow.find('.product-name-input').val() || code));
                }
                
                // Focus on product name input
                setTimeout(() => newRow.find('.product-name-input').focus(), 100);
                
                itemCount++;
                updateTotals();
            }

            // Client-side SKU generator for quick fills
            function generateClientSku(name) {
                const prefix = 'NG';
                const namePart = (name || 'X').toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0,3) || 'X';
                const ts = Date.now().toString().slice(-5);
                const rand = Math.floor(100 + Math.random() * 900);
                return `${prefix}-${namePart}-${ts}${rand}`;
            }

            // Add new item row manually
            $(document).on('click', '#add-item-btn', function(e) {
                e.preventDefault();
                let template = $('#item-row-template').html();
                template = template.replace(/INDEX/g, itemCount);
                
                $('#no-items-row').hide();
                $('#items-container').append(template);
                itemCount++;
                updateTotals();
            });

            // Product selection - auto-fill price and SKU/EAN with clean name
            $(document).on('change', '.product-select', function() {
                let selectedOption = $(this).find(':selected');
                let productId = selectedOption.val();
                let productName = selectedOption.data('name') || selectedOption.text();
                let price = selectedOption.data('price') || 0;
                let sku = selectedOption.data('sku') || '';
                let barcode = selectedOption.data('barcode') || '';

                let row = $(this).closest('tr');
                row.find('.product-name').val(productName);
                row.find('.sku-input').val(sku || barcode);
                row.find('.ean-input').val(barcode);
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

            // Generate SKU button handler (for temp rows)
            $(document).on('click', '.generate-sku-btn', function(e) {
                e.preventDefault();
                const row = $(this).closest('tr');
                const name = row.find('.product-name-input').val() || row.find('.ean-input').val() || 'X';
                row.find('.sku-input').val(generateClientSku(name));
            });

            // Calculate totals when quantities or costs change
            $(document).on('input', '.expected-qty, .unit-cost, .product-name-input', function() {
                let row = $(this).closest('tr');
                calculateRowTotal(row);
                updateTotals();
            });

            // Auto-fill received quantity
            $(document).on('input', '.expected-qty', function() {
                let expectedQty = $(this).val();
                let receivedQtyInput = $(this).closest('tr').find('.received-qty');
                
                // Auto-fill if received is empty or 0
                if (!receivedQtyInput.val() || receivedQtyInput.val() == 0) {
                    receivedQtyInput.val(expectedQty);
                }
            });

            function calculateRowTotal(row) {
                let expectedQty = parseFloat(row.find('.expected-qty').val()) || 0;
                let unitCost = parseFloat(row.find('.unit-cost').val()) || 0;
                let totalCost = expectedQty * unitCost;
                
                row.find('.total-cost').text('$' + totalCost.toFixed(2));
            }

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

            function showNotification(type, message) {
                let bgColor = type === 'success' ? '#d4edda' : '#fff3cd';
                let textColor = type === 'success' ? '#155724' : '#856404';
                
                let notification = $('<div>')
                    .css({
                        position: 'fixed',
                        top: '20px',
                        right: '20px',
                        background: bgColor,
                        color: textColor,
                        padding: '15px 20px',
                        borderRadius: '6px',
                        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                        zIndex: 9999,
                        fontWeight: '500',
                        minWidth: '250px'
                    })
                    .html('<i class="fa fa-' + (type === 'success' ? 'check' : 'exclamation-triangle') + '"></i> ' + message)
                    .appendTo('body');
                
                setTimeout(() => notification.fadeOut(() => notification.remove()), 3000);
            }

            // Form validation
            $('#incoming-form').submit(function(e) {
                let hasItems = $('.item-row').length > 0;
                
                if (!hasItems) {
                    e.preventDefault();
                    alert('{{ trans('Please add at least one item to receive.') }}');
                    return false;
                }

                // Validate temporary products have names
                let missingName = false;
                $('.temp-product-row').each(function() {
                    let productName = $(this).find('.product-name-input').val().trim();
                    if (!productName) {
                        missingName = true;
                        $(this).find('.product-name-input').css('border-color', 'red');
                    }
                });

                if (missingName) {
                    e.preventDefault();
                    alert('{{ trans('Please enter product names for all new/temporary items.') }}');
                    return false;
                }

                return true;
            });

            // Global keyboard shortcut: Focus barcode input with F2, Camera with F3 + hardware scanner buffer
            $(document).on('keydown', function(e) {
                const tag = e.target.tagName ? e.target.tagName.toLowerCase() : '';
                const isInput = tag === 'input' || tag === 'textarea' || e.target.isContentEditable;

                // Hardware scanner: collect fast keypresses ending with Enter
                if (!isScanning && (!isInput || e.target.id === 'barcode-input')) {
                    if (e.key === 'Enter') {
                        if (hardwareBuffer.length >= 3) {
                            $('#barcode-input').val(hardwareBuffer);
                            lookupAndAddProduct();
                        }
                        hardwareBuffer = '';
                        return;
                    }
                    if (e.key && e.key.length === 1) {
                        hardwareBuffer += e.key;
                        clearTimeout(hardwareTimer);
                        hardwareTimer = setTimeout(() => { hardwareBuffer = ''; }, 200);
                    }
                }

                if (e.key === 'F2') {
                    e.preventDefault();
                    $('#barcode-input').focus().select();
                }
                if (e.key === 'F3') {
                    e.preventDefault();
                    if (!isScanning) {
                        startCameraScanner();
                    } else {
                        stopCameraScanner();
                    }
                }
                // ESC to close scanner
                if (e.key === 'Escape' && isScanning) {
                    stopCameraScanner();
                }
            });

            // Close modal when clicking outside
            $('#camera-scanner-modal').on('click', function(e) {
                if (e.target === this) {
                    stopCameraScanner();
                }
            });
        });
    </script>
@endsection

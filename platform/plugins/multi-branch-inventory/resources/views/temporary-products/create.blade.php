@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-0">Create Temporary Product</h3>
                <p class="text-muted mb-0">Add an unknown product to the temporary stock list</p>
            </div>
            <div>
                <a href="{{ route('temporary-products.index') }}" class="btn btn-light">Back to list</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('temporary-products.store') }}">
                @csrf
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control" value="{{ old('sku') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">EAN / Barcode</label>
                        <input type="text" name="ean" class="form-control" value="{{ old('ean') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Product Code</label>
                        <input type="text" name="product_code" class="form-control" value="{{ old('product_code') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="{{ old('quantity', 1) }}" min="1">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Selling Price (optional)</label>
                        <input type="text" name="selling_price" class="form-control" value="{{ old('selling_price') }}" placeholder="0.00">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cost Price (optional)</label>
                        <input type="text" name="cost_price" class="form-control" value="{{ old('cost_price') }}" placeholder="0.00">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Storage Location (optional)</label>
                    <input type="text" name="storage_location" class="form-control" value="{{ old('storage_location') }}">
                    <small class="form-text text-muted">E.g. Shelf B / Bin 12 or A1-R3-S2</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('temporary-products.index') }}" class="btn btn-light me-2">Cancel</a>
                    <button class="btn btn-primary">Create Temporary Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

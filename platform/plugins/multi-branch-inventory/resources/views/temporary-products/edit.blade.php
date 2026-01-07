@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-0">Edit Temporary Product #{{ $temporaryProduct->id }}</h3>
                <p class="text-muted mb-0">Modify temporary product details</p>
            </div>
            <div>
                <a href="{{ route('temporary-products.show', $temporaryProduct->id) }}" class="btn btn-light">Back</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('temporary-products.update', $temporaryProduct->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $temporaryProduct->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $temporaryProduct->name }}" required>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control" value="{{ $temporaryProduct->sku }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">EAN</label>
                        <input type="text" name="ean" class="form-control" value="{{ $temporaryProduct->ean }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Product Code</label>
                        <input type="text" name="product_code" class="form-control" value="{{ $temporaryProduct->product_code }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="{{ $temporaryProduct->quantity }}" min="0">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Selling Price</label>
                        <input type="text" name="selling_price" class="form-control" value="{{ $temporaryProduct->selling_price }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cost Price</label>
                        <input type="text" name="cost_price" class="form-control" value="{{ $temporaryProduct->cost_price }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Storage Location</label>
                    <input type="text" name="storage_location" class="form-control" value="{{ $temporaryProduct->storage_location }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3">{{ $temporaryProduct->notes }}</textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('temporary-products.index') }}" class="btn btn-light me-2">Cancel</a>
                    <button class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

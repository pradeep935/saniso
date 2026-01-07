@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-0">Temporary Product #{{ $temporaryProduct->id }}</h3>
                <p class="text-muted mb-0">{{ $temporaryProduct->name }}</p>
            </div>
            <div>
                <a href="{{ route('temporary-products.edit', $temporaryProduct->id) }}" class="btn btn-secondary me-2">Edit</a>
                <a href="{{ route('temporary-products.index') }}" class="btn btn-light">Back to list</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Name:</strong> {{ $temporaryProduct->name }}</p>
                    <p><strong>SKU:</strong> {{ $temporaryProduct->sku }}</p>
                    <p><strong>EAN:</strong> {{ $temporaryProduct->ean }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Quantity:</strong> {{ $temporaryProduct->quantity }}</p>
                    <p><strong>Branch:</strong> {{ $temporaryProduct->branch->name ?? 'N/A' }}</p>
                    <p><strong>Status:</strong> {{ ucfirst($temporaryProduct->status) }}</p>
                </div>
            </div>

            @if($temporaryProduct->notes)
                <hr>
                <p><strong>Notes:</strong></p>
                <p>{{ $temporaryProduct->notes }}</p>
            @endif

            <div class="mt-3">
                <form method="POST" action="{{ route('temporary-products.convert', $temporaryProduct->id) }}" onsubmit="return confirm('Convert this temporary product to a permanent product?')">
                    @csrf
                    <button type="submit" class="btn btn-success">Convert to Product</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

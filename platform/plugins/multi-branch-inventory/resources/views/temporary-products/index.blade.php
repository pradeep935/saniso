@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-0">Temporary / New Goods</h3>
                <p class="text-muted mb-0">Manage items that arrived but are not yet regular products</p>
            </div>
            <div class="text-end">
                <a href="{{ route('temporary-products.create') }}" class="btn btn-primary me-2">
                    <i class="fa fa-plus me-1"></i> Create Temporary Product
                </a>
                <a href="{{ route('temporary-products.export-excel', request()->only('branch_id')) }}" class="btn btn-outline-secondary">
                    <i class="fa fa-file-csv me-1"></i> Export CSV
                </a>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name, sku or ean">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Any</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                        <option value="sold_out" {{ request('status') == 'sold_out' ? 'selected' : '' }}>Sold Out</option>
                    </select>
                </div>

                <div class="col-md-1 text-end">
                    <button class="btn btn-primary mt-2"><i class="fa fa-search me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($temporaryProducts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th width="6%">ID</th>
                                <th>Name</th>
                                <th width="18%">SKU / EAN</th>
                                <th class="text-center" width="8%">Qty</th>
                                <th width="12%">Branch</th>
                                <th width="10%">Status</th>
                                <th class="text-center" width="16%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($temporaryProducts as $p)
                                <tr>
                                    <td>{{ $p->id }}</td>
                                    <td>{{ $p->name }}</td>
                                    <td>{{ $p->sku }}<br><small class="text-muted">{{ $p->ean }}</small></td>
                                    <td class="text-center">{{ $p->quantity }}</td>
                                    <td>{{ $p->branch->name ?? 'N/A' }}</td>
                                    <td>{{ ucfirst($p->status) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('temporary-products.show', $p->id) }}" class="btn btn-sm btn-info me-1">View</a>
                                        <a href="{{ route('temporary-products.edit', $p->id) }}" class="btn btn-sm btn-secondary me-1">Edit</a>
                                        <form method="POST" action="{{ route('temporary-products.convert', $p->id) }}" style="display:inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Convert this temporary product to a permanent product?')">Convert</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $temporaryProducts->appends(request()->query())->links() }}
                </div>
            @else
                <div class="alert alert-info m-3">No temporary products found.</div>
            @endif
        </div>
    </div>
</div>
@endsection

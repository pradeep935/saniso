{{-- Branch Inventory Management Tab Content --}}
<div class="branch-inventory-tab-content">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h5>{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.inventory_management') }}</h5>
                <div>
                    <a href="{{ route('branch-inventory.bulk-update', ['branch_id' => '', 'product_filter' => $product->id]) }}" class="btn btn-info">
                        <i class="fa fa-cubes"></i> {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.bulk_update_title') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Product & Ecommerce Info --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Product Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Name:</strong> {{ $product->name }}
                    </div>
                    <div class="mb-2">
                        <strong>SKU:</strong> {{ $product->sku }}
                    </div>
                    <div class="mb-2">
                        <strong>Ecommerce Price:</strong> ${{ number_format($product->price, 2) }}
                    </div>
                    <div class="mb-2">
                        <strong>Ecommerce Stock:</strong> 
                        <span class="badge badge-primary">{{ $product->quantity }} units</span>
                    </div>
                    <div>
                        <strong>Status:</strong> 
                        <span class="badge {{ $product->status == 'published' ? 'badge-success' : 'badge-secondary' }}">
                            {{ ucfirst($product->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Branch Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Branch</th>
                                    <th>Type</th>
                                    <th>Stock</th>
                                    <th>Reserved</th>
                                    <th>Available</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branches as $branch)
                                    @php
                                        $inventory = $branchInventories->get($branch->id);
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $branch->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $branch->code }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ ucfirst($branch->type) }}</span>
                                        </td>
                                        <td>
                                            @if($inventory)
                                                <span class="badge {{ $inventory->quantity_on_hand > 0 ? 'badge-success' : 'badge-secondary' }}">
                                                    {{ $inventory->quantity_on_hand }}
                                                </span>
                                            @else
                                                <span class="badge badge-light">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($inventory)
                                                <span class="badge badge-warning">{{ $inventory->quantity_reserved ?? 0 }}</span>
                                            @else
                                                <span class="badge badge-light">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($inventory)
                                                <span class="badge {{ $inventory->quantity_available > 0 ? 'badge-success' : 'badge-secondary' }}">
                                                    {{ $inventory->quantity_available }}
                                                </span>
                                            @else
                                                <span class="badge badge-light">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($inventory)
                                                ${{ number_format($inventory->selling_price ?? $product->price, 2) }}
                                            @else
                                                ${{ number_format($product->price, 2) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($inventory)
                                                <a href="{{ route('branch-inventory.edit', $inventory->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('branch-inventory.create', ['branch_id' => $branch->id, 'product_id' => $product->id]) }}" 
                                                   class="btn btn-sm btn-success" title="Add to Branch">
                                                    <i class="fa fa-plus"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold">
                                    <td colspan="2">Total</td>
                                    <td>
                                        <span class="badge badge-primary">
                                            {{ $branchInventories->sum('quantity_on_hand') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-warning">
                                            {{ $branchInventories->sum('quantity_reserved') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">
                                            {{ $branchInventories->sum('quantity_available') }}
                                        </span>
                                    </td>
                                    <td colspan="2">
                                        <small class="text-muted">
                                            Should match ecommerce total: {{ $product->quantity }}
                                        </small>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="btn-group" role="group">
                        <a href="{{ route('branch-inventory.bulk-update', ['branch_id' => '', 'product_filter' => $product->id]) }}" 
                           class="btn btn-info">
                            <i class="fa fa-cubes"></i> {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.manage_all_branches') }}
                        </a>
                        
                        <a href="{{ route('stock-transfers.create', ['product_id' => $product->id]) }}" 
                           class="btn btn-warning">
                            <i class="fa fa-exchange-alt"></i> {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.transfer_stock') }}
                        </a>
                        
                        <a href="{{ route('incoming-goods.create', ['product_id' => $product->id]) }}" 
                           class="btn btn-success">
                            <i class="fa fa-truck"></i> {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.receive_goods') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Navigation Help --}}
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h6 class="alert-heading">
                    <i class="fa fa-lightbulb"></i> {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.navigation_structure') }}
                </h6>
                <p class="mb-2">
                    {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.structure_help') }}
                </p>
                <hr>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('branches.index') }}">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.branches') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('products.index') }}">{{ trans('plugins/ecommerce::products.name') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.inventory_management') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
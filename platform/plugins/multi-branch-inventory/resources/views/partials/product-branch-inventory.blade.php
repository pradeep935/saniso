{{-- Branch Inventory Summary for Product General Info --}}
<div class="branch-inventory-summary mt-3">
    <h5 class="mb-3">
        <i class="ti ti-building-warehouse"></i> 
        {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.inventory_management') }}
    </h5>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.branch_stock') }} Summary</h6>
                </div>
                <div class="card-body">
                    @if($branchInventories->count() > 0)
                        <div class="row">
                            @foreach($branchInventories as $inventory)
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3">
                                        <h6 class="text-primary mb-1">{{ $inventory->branch->name }}</h6>
                                        <div class="d-flex justify-content-between">
                                            <span>{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.quantity') }}:</span>
                                            <strong class="badge {{ $inventory->quantity_available > 0 ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $inventory->quantity_available }}
                                            </strong>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <span>Reserved:</span>
                                            <span class="badge badge-warning">{{ $inventory->quantity_reserved ?? 0 }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <span>Price:</span>
                                            <span>${{ number_format($inventory->selling_price ?? $product->price, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-3 text-center">
                            <a href="{{ route('branch-inventory.bulk-update', ['product_id' => $product->id]) }}" class="btn btn-primary">
                                <i class="fa fa-edit"></i> {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.manage_all_branches') }}
                            </a>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fa fa-info-circle"></i>
                            {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.no_branch_inventory') }}
                            <br>
                            <a href="{{ route('branch-inventory.bulk-update', ['product_id' => $product->id]) }}" class="btn btn-sm btn-primary mt-2">
                                {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.add_to_branches') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header')
<!-- Multi-Branch Inventory Branches CSS -->
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/base.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/branches.css') }}">

<!-- Fix for zero height issue -->
<style>
.card {
    min-height: 200px !important;
    display: block !important;
}

.card-body {
    min-height: 150px !important;
    display: block !important;
}

.table-responsive {
    display: block !important;
    overflow-x: auto !important;
}

.table {
    display: table !important;
    width: 100% !important;
}

.container-fluid {
    display: block !important;
    min-height: 400px !important;
}

/* Ensure content is visible */
.page-content,
.content,
.main-content {
    display: block !important;
    min-height: 300px !important;
}
</style>

<!-- Fix Height Issue -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Force content to be visible
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.style.display = 'block';
        card.style.minHeight = '200px';
        card.style.visibility = 'visible';
    });
    
    const cardBodies = document.querySelectorAll('.card-body');
    cardBodies.forEach(body => {
        body.style.display = 'block';
        body.style.minHeight = '150px';
        body.style.visibility = 'visible';
    });
    
    const containers = document.querySelectorAll('.container-fluid');
    containers.forEach(container => {
        container.style.display = 'block';
        container.style.minHeight = '400px';
        container.style.visibility = 'visible';
    });
    
    console.log('✅ Height fix applied - page should now be visible');
});
</script>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h1 class="page-title mb-1">
                    <i class="ti ti-building mr-2"></i>{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.branches') }}
                </h1>
                <p class="text-muted mb-0">Manage all your business locations and branches</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('branches.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus mr-1"></i> {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.create') }}
                </a>
                <a href="{{ route('branch-inventory.index') }}" class="btn btn-outline-primary">
                    <i class="ti ti-package mr-1"></i> View Inventory
                </a>
            </div>
        </div>
    </div>

    <!-- Branches Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Branch Management</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Branch Name</th>
                            <th>Branch Code</th>
                            <th>Type</th>
                            <th>Address</th>
                            <th>Manager</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branches as $branch)
                            <tr @if($branch->is_main_branch) class="table-warning" @endif>
                                <td>
                                    <strong>{{ $branch->name }}</strong>
                                    @if($branch->is_main_branch)
                                        <span class="badge badge-warning ml-1">👑 Main</span>
                                    @endif
                                </td>
                                <td><code>{{ $branch->code }}</code></td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $branch->type_icon ?? '🏢' }} {{ $branch->type_name ?? 'Store' }}
                                    </span>
                                </td>
                                <td>{{ $branch->address ?: 'N/A' }}</td>
                                <td>{{ $branch->manager_name ?: 'N/A' }}</td>
                                <td>
                                    @if($branch->status === 'active')
                                        <span class="badge badge-success">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.active') }}</span>
                                    @else
                                        <span class="badge badge-danger">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.inactive') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-sm btn-info">
                                            <i class="ti ti-edit"></i> {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.edit') }}
                                        </a>
                                        <form method="POST" action="{{ route('branches.destroy', $branch->id) }}" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this branch?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="ti ti-trash"></i> {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="ti ti-building-store display-4 text-muted"></i>
                                        <h5 class="mt-2">No branches found</h5>
                                        <p class="text-muted">Create your first branch to get started with inventory management.</p>
                                        <a href="{{ route('branches.create') }}" class="btn btn-primary">
                                            <i class="ti ti-plus mr-1"></i> Create First Branch
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($branches->hasPages())
                <div class="mt-3">
                    {{ $branches->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
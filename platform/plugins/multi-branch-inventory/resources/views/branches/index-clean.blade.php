@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h1 class="page-title mb-1">
                    📍 {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.branches') }}
                </h1>
                <p class="text-muted mb-0">Manage all your business locations and branches</p>
            </div>
            <div class="page-actions">
                @if(Route::has('branches.create'))
                    <a href="{{ route('branches.create') }}" class="btn btn-primary">
                        ➕ {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.create') }}
                    </a>
                @endif
                @if(Route::has('branch-inventory.index'))
                    <a href="{{ route('branch-inventory.index') }}" class="btn btn-outline-primary">
                        📦 View Inventory
                    </a>
                @endif
            </div>
        </div>
    </div>

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
                                        <span class="badge bg-warning text-dark ms-1">👑 Main</span>
                                    @endif
                                </td>
                                <td><code>{{ $branch->code }}</code></td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $branch->type_icon ?? '🏢' }} {{ $branch->type_name ?? 'Store' }}
                                    </span>
                                </td>
                                <td>{{ $branch->address ?: 'N/A' }}</td>
                                <td>{{ $branch->manager_name ?: 'N/A' }}</td>
                                <td>
                                    @if($branch->status === 'active')
                                        <span class="badge bg-success">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.active') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ trans('plugins/multi-branch-inventory::multi-branch-inventory.inactive') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Branch actions">
                                        @if(Route::has('branches.edit'))
                                            <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-sm btn-outline-primary">
                                                ✏️ {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.edit') }}
                                            </a>
                                        @endif
                                        @if(Route::has('branches.destroy') && !$branch->is_main_branch)
                                            <form method="POST" action="{{ route('branches.destroy', $branch->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this branch?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    🗑️ {{ trans('plugins/multi-branch-inventory::multi-branch-inventory.delete') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-store-alt display-4 text-muted mb-3"></i>
                                        <h5 class="mt-2">No branches found</h5>
                                        <p class="text-muted">Create your first branch to get started with inventory management.</p>
                                        @if(Route::has('branches.create'))
                                            <a href="{{ route('branches.create') }}" class="btn btn-primary">
                                                ➕ Create First Branch
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($branches->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $branches->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
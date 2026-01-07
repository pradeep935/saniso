@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <h1 class="page-title">📍 Branches Management</h1>
        <p class="text-muted">Manage all your business locations and branches</p>
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
                                        <span class="badge bg-warning text-dark ml-1">👑 Main</span>
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
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="#" class="btn btn-sm btn-info">✏️ Edit</a>
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">🗑️ Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <h5>No branches found</h5>
                                    <p class="text-muted">Create your first branch to get started.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($branches->hasPages())
                <div class="mt-3">{{ $branches->links() }}</div>
            @endif
        </div>
    </div>
</div>
        <li>View loaded at: {{ now() }}</li>
    </ul>
    
    <div style="margin: 20px 0;">
        <a href="{{ route('branches.create') }}" style="background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;">
            Create New Branch
        </a>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <thead>
            <tr style="background: #f8f9fa;">
                <th style="border: 1px solid #dee2e6; padding: 10px;">Name</th>
                <th style="border: 1px solid #dee2e6; padding: 10px;">Code</th>
                <th style="border: 1px solid #dee2e6; padding: 10px;">Type</th>
                <th style="border: 1px solid #dee2e6; padding: 10px;">Status</th>
                <th style="border: 1px solid #dee2e6; padding: 10px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($branches as $branch)
                <tr style="border: 1px solid #dee2e6;">
                    <td style="border: 1px solid #dee2e6; padding: 10px;">
                        <strong>{{ $branch->name }}</strong>
                        @if($branch->is_main_branch)
                            <span style="background: #ffc107; color: #000; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Main</span>
                        @endif
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 10px;">{{ $branch->code }}</td>
                    <td style="border: 1px solid #dee2e6; padding: 10px;">{{ $branch->type_name }}</td>
                    <td style="border: 1px solid #dee2e6; padding: 10px;">
                        <span style="background: {{ $branch->status === 'active' ? '#198754' : '#dc3545' }}; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px;">
                            {{ $branch->status }}
                        </span>
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 10px;">
                        <a href="{{ route('branches.edit', $branch->id) }}" style="background: #17a2b8; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; margin-right: 5px;">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('branches.destroy', $branch->id) }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: #dc3545; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer;" onclick="return confirm('Are you sure?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="border: 1px solid #dee2e6; padding: 20px; text-align: center;">
                        No branches found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin: 20px 0;">
        {{ $branches->links() }}
    </div>
</div>
@endsection
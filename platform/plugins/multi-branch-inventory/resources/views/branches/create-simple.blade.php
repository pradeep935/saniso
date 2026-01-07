@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary me-3">
                ← Back to Branches
            </a>
            <div>
                <h1 class="page-title mb-1">➕ Create New Branch</h1>
                <p class="text-muted mb-0">Add a new business location or branch</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Branch Information</h3>
        </div>
        <form method="POST" action="{{ route('branches.store') }}">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Branch Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="code" class="form-label">Branch Code *</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                   id="code" name="code" value="{{ old('code') }}" required>
                            <div class="form-text">Unique identifier for this branch (e.g., MAIN001)</div>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Branch Type *</label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="store" {{ old('type') === 'store' ? 'selected' : '' }}>🏢 Store</option>
                                <option value="warehouse" {{ old('type') === 'warehouse' ? 'selected' : '' }}>📦 Warehouse</option>
                                <option value="distribution_center" {{ old('type') === 'distribution_center' ? 'selected' : '' }}>🚛 Distribution Center</option>
                                <option value="outlet" {{ old('type') === 'outlet' ? 'selected' : '' }}>🏪 Outlet</option>
                                <option value="flagship" {{ old('type') === 'flagship' ? 'selected' : '' }}>⭐ Flagship Store</option>
                                <option value="franchise" {{ old('type') === 'franchise' ? 'selected' : '' }}>🤝 Franchise</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="manager_name" class="form-label">Manager Name</label>
                            <input type="text" class="form-control @error('manager_name') is-invalid @enderror" 
                                   id="manager_name" name="manager_name" value="{{ old('manager_name') }}">
                            @error('manager_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="timezone" class="form-label">Timezone *</label>
                            <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone" required>
                                <option value="">Select Timezone</option>
                                <option value="UTC" {{ old('timezone') === 'UTC' ? 'selected' : '' }}>UTC</option>
                                <option value="America/New_York" {{ old('timezone') === 'America/New_York' ? 'selected' : '' }}>Eastern Time (US)</option>
                                <option value="America/Chicago" {{ old('timezone') === 'America/Chicago' ? 'selected' : '' }}>Central Time (US)</option>
                                <option value="America/Denver" {{ old('timezone') === 'America/Denver' ? 'selected' : '' }}>Mountain Time (US)</option>
                                <option value="America/Los_Angeles" {{ old('timezone') === 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time (US)</option>
                                <option value="Europe/London" {{ old('timezone') === 'Europe/London' ? 'selected' : '' }}>London</option>
                                <option value="Europe/Paris" {{ old('timezone') === 'Europe/Paris' ? 'selected' : '' }}>Paris</option>
                                <option value="Asia/Tokyo" {{ old('timezone') === 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo</option>
                            </select>
                            @error('timezone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_main_branch" name="is_main_branch" value="1" {{ old('is_main_branch') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_main_branch">
                            👑 Set as Main Branch
                        </label>
                        <div class="form-text">Only one branch can be the main branch. This will update the previous main branch.</div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('branches.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        ✅ Create Branch
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
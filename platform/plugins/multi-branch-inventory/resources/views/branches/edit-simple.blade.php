@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary me-3">
                ← Back to Branches
            </a>
            <div>
                <h1 class="page-title mb-1">✏️ Edit Branch: {{ $branch->name }}</h1>
                <p class="text-muted mb-0">Update branch information</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Branch Information</h3>
        </div>
        <form method="POST" action="{{ route('branches.update', $branch->id) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Branch Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $branch->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="code" class="form-label">Branch Code *</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                   id="code" name="code" value="{{ old('code', $branch->code) }}" required>
                            <div class="form-text">Unique identifier for this branch</div>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Branch Type *</label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="store" {{ old('type', $branch->type) === 'store' ? 'selected' : '' }}>🏢 Store</option>
                                <option value="warehouse" {{ old('type', $branch->type) === 'warehouse' ? 'selected' : '' }}>📦 Warehouse</option>
                                <option value="distribution_center" {{ old('type', $branch->type) === 'distribution_center' ? 'selected' : '' }}>🚛 Distribution Center</option>
                                <option value="outlet" {{ old('type', $branch->type) === 'outlet' ? 'selected' : '' }}>🏪 Outlet</option>
                                <option value="flagship" {{ old('type', $branch->type) === 'flagship' ? 'selected' : '' }}>⭐ Flagship Store</option>
                                <option value="franchise" {{ old('type', $branch->type) === 'franchise' ? 'selected' : '' }}>🤝 Franchise</option>
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
                                      id="address" name="address" rows="3">{{ old('address', $branch->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="manager_name" class="form-label">Manager Name</label>
                            <input type="text" class="form-control @error('manager_name') is-invalid @enderror" 
                                   id="manager_name" name="manager_name" value="{{ old('manager_name', $branch->manager_name) }}">
                            @error('manager_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $branch->phone) }}">
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
                                   id="email" name="email" value="{{ old('email', $branch->email) }}">
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
                                <option value="UTC" {{ old('timezone', $branch->timezone) === 'UTC' ? 'selected' : '' }}>UTC</option>
                                <option value="America/New_York" {{ old('timezone', $branch->timezone) === 'America/New_York' ? 'selected' : '' }}>Eastern Time (US)</option>
                                <option value="America/Chicago" {{ old('timezone', $branch->timezone) === 'America/Chicago' ? 'selected' : '' }}>Central Time (US)</option>
                                <option value="America/Denver" {{ old('timezone', $branch->timezone) === 'America/Denver' ? 'selected' : '' }}>Mountain Time (US)</option>
                                <option value="America/Los_Angeles" {{ old('timezone', $branch->timezone) === 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time (US)</option>
                                <option value="Europe/London" {{ old('timezone', $branch->timezone) === 'Europe/London' ? 'selected' : '' }}>London</option>
                                <option value="Europe/Paris" {{ old('timezone', $branch->timezone) === 'Europe/Paris' ? 'selected' : '' }}>Paris</option>
                                <option value="Asia/Tokyo" {{ old('timezone', $branch->timezone) === 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo</option>
                            </select>
                            @error('timezone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', $branch->status) === 'active' ? 'selected' : '' }}>✅ Active</option>
                                <option value="inactive" {{ old('status', $branch->status) === 'inactive' ? 'selected' : '' }}>❌ Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3 pt-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_main_branch" name="is_main_branch" value="1" 
                                       {{ old('is_main_branch', $branch->is_main_branch) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_main_branch">
                                    👑 Set as Main Branch
                                </label>
                                <div class="form-text">Only one branch can be the main branch.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('branches.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        💾 Update Branch
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
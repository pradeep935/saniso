@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header')
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/base.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/branches.css') }}">
@endpush

@section('content')
    <div class="max-width-1200">
        <div class="flexbox-annotated-section">
            <div class="flexbox-annotated-section-annotation">
                <div class="annotated-section-title pd-all-20">
                    <h2>{{ $branch->exists ? 'Edit Branch' : 'Create Branch' }}</h2>
                </div>
                <div class="annotated-section-description pd-all-20 p-none-t">
                    <p class="color-note">{{ $branch->exists ? 'Update branch information' : 'Add a new branch location' }}</p>
                </div>
            </div>

            <div class="flexbox-annotated-section-content">
                <div class="wrapper-content pd-all-20">
                    <form method="POST" action="{{ $branch->exists ? route('branches.update', $branch->id) : route('branches.store') }}">
                        @csrf
                        @if($branch->exists)
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-8">
                                <div class="main-form">
                                    <div class="form-body">
                                        <!-- Branch Name -->
                                        <div class="form-group mb-3 @error('name') has-error @enderror">
                                            <label for="name" class="control-label required">Branch Name</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="{{ old('name', $branch->name) }}" required>
                                            @error('name')
                                                <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Branch Code -->
                                        <div class="form-group mb-3 @error('code') has-error @enderror">
                                            <label for="code" class="control-label required">Branch Code</label>
                                            <input type="text" class="form-control" id="code" name="code" 
                                                   value="{{ old('code', $branch->code) }}" required 
                                                   placeholder="e.g., MAIN, NORTH, SOUTH">
                                            <span class="help-block">Unique identifier for this branch</span>
                                            @error('code')
                                                <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Branch Type -->
                                        <div class="form-group mb-3 @error('type') has-error @enderror">
                                            <label for="type" class="control-label required">Branch Type</label>
                                            <select class="form-control" id="type" name="type" required>
                                                <option value="">Select Branch Type</option>
                                                <option value="store" {{ old('type', $branch->type) == 'store' ? 'selected' : '' }}>
                                                    🏪 Retail Store
                                                </option>
                                                <option value="warehouse" {{ old('type', $branch->type) == 'warehouse' ? 'selected' : '' }}>
                                                    🏭 Warehouse
                                                </option>
                                                <option value="distribution_center" {{ old('type', $branch->type) == 'distribution_center' ? 'selected' : '' }}>
                                                    📦 Distribution Center
                                                </option>
                                                <option value="outlet" {{ old('type', $branch->type) == 'outlet' ? 'selected' : '' }}>
                                                    🛍️ Outlet Store
                                                </option>
                                                <option value="flagship" {{ old('type', $branch->type) == 'flagship' ? 'selected' : '' }}>
                                                    ⭐ Flagship Store
                                                </option>
                                                <option value="pop_up" {{ old('type', $branch->type) == 'pop_up' ? 'selected' : '' }}>
                                                    🎪 Pop-up Store
                                                </option>
                                                <option value="showroom" {{ old('type', $branch->type) == 'showroom' ? 'selected' : '' }}>
                                                    🖼️ Showroom
                                                </option>
                                                <option value="kiosk" {{ old('type', $branch->type) == 'kiosk' ? 'selected' : '' }}>
                                                    🏪 Kiosk
                                                </option>
                                                <option value="franchise" {{ old('type', $branch->type) == 'franchise' ? 'selected' : '' }}>
                                                    🤝 Franchise
                                                </option>
                                                <option value="online_fulfillment" {{ old('type', $branch->type) == 'online_fulfillment' ? 'selected' : '' }}>
                                                    💻 Online Fulfillment Center
                                                </option>
                                            </select>
                                            @error('type')
                                                <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Address -->
                                        <div class="form-group mb-3 @error('address') has-error @enderror">
                                            <label for="address" class="control-label">Address</label>
                                            <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $branch->address) }}</textarea>
                                            @error('address')
                                                <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="row">
                                            <!-- City -->
                                            <div class="col-md-6">
                                                <div class="form-group mb-3 @error('city') has-error @enderror">
                                                    <label for="city" class="control-label">City</label>
                                                    <input type="text" class="form-control" id="city" name="city" 
                                                           value="{{ old('city', $branch->city) }}">
                                                    @error('city')
                                                        <span class="help-block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Country -->
                                            <div class="col-md-6">
                                                <div class="form-group mb-3 @error('country') has-error @enderror">
                                                    <label for="country" class="control-label">Country</label>
                                                    <input type="text" class="form-control" id="country" name="country" 
                                                           value="{{ old('country', $branch->country) }}">
                                                    @error('country')
                                                        <span class="help-block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- Postal Code -->
                                            <div class="col-md-6">
                                                <div class="form-group mb-3 @error('postal_code') has-error @enderror">
                                                    <label for="postal_code" class="control-label">Postal Code</label>
                                                    <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                                           value="{{ old('postal_code', $branch->postal_code) }}">
                                                    @error('postal_code')
                                                        <span class="help-block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Phone -->
                                            <div class="col-md-6">
                                                <div class="form-group mb-3 @error('phone') has-error @enderror">
                                                    <label for="phone" class="control-label">Phone</label>
                                                    <input type="text" class="form-control" id="phone" name="phone" 
                                                           value="{{ old('phone', $branch->phone) }}">
                                                    @error('phone')
                                                        <span class="help-block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Email -->
                                        <div class="form-group mb-3 @error('email') has-error @enderror">
                                            <label for="email" class="control-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="{{ old('email', $branch->email) }}">
                                            @error('email')
                                                <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Manager Name -->
                                        <div class="form-group mb-3 @error('manager_name') has-error @enderror">
                                            <label for="manager_name" class="control-label">Manager Name</label>
                                            <input type="text" class="form-control" id="manager_name" name="manager_name" 
                                                   value="{{ old('manager_name', $branch->manager_name) }}">
                                            @error('manager_name')
                                                <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Timezone -->
                                        <div class="form-group mb-3 @error('timezone') has-error @enderror">
                                            <label for="timezone" class="control-label">Timezone</label>
                                            <select class="form-control" id="timezone" name="timezone">
                                                <option value="UTC" {{ old('timezone', $branch->timezone) == 'UTC' ? 'selected' : '' }}>UTC</option>
                                                <option value="America/New_York" {{ old('timezone', $branch->timezone) == 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                                <option value="America/Chicago" {{ old('timezone', $branch->timezone) == 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                                <option value="America/Denver" {{ old('timezone', $branch->timezone) == 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                                <option value="America/Los_Angeles" {{ old('timezone', $branch->timezone) == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                                <option value="Europe/London" {{ old('timezone', $branch->timezone) == 'Europe/London' ? 'selected' : '' }}>London (GMT)</option>
                                                <option value="Europe/Paris" {{ old('timezone', $branch->timezone) == 'Europe/Paris' ? 'selected' : '' }}>Paris (CET)</option>
                                                <option value="Asia/Tokyo" {{ old('timezone', $branch->timezone) == 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo (JST)</option>
                                                <option value="Asia/Shanghai" {{ old('timezone', $branch->timezone) == 'Asia/Shanghai' ? 'selected' : '' }}>Shanghai (CST)</option>
                                                <option value="Australia/Sydney" {{ old('timezone', $branch->timezone) == 'Australia/Sydney' ? 'selected' : '' }}>Sydney (AEST)</option>
                                            </select>
                                            @error('timezone')
                                                <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-actions-wrapper">
                                    <div class="widget meta-boxes form-actions form-actions-default action-horizontal">
                                        <div class="widget-title">
                                            <h4><span>Settings</span></h4>
                                        </div>
                                        <div class="widget-body">
                                            <div class="btn-set">
                                                <button type="submit" class="btn btn-info">
                                                    <i class="fa fa-save"></i> {{ $branch->exists ? 'Update Branch' : 'Save Branch' }}
                                                </button>
                                                <button type="button" class="btn btn-default" onclick="window.history.back()">
                                                    <i class="fa fa-arrow-left"></i> Cancel
                                                </button>
                                            </div>

                                            <!-- Status -->
                                            <div class="form-group mb-3">
                                                <label for="status" class="control-label">Status</label>
                                                <select class="form-control" id="status" name="status">
                                                    <option value="active" {{ old('status', $branch->status) == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ old('status', $branch->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                            </div>

                                            <!-- Main Branch -->
                                            <div class="form-group mb-3">
                                                <label class="control-label">
                                                    <input type="checkbox" name="is_main_branch" value="1" 
                                                           id="is_main_branch"
                                                           {{ old('is_main_branch', $branch->is_main_branch) ? 'checked' : '' }}>
                                                    Main Branch 👑
                                                </label>
                                                <span class="help-block">
                                                    This branch will be used as the primary location
                                                    @php
                                                        $currentMain = \Botble\MultiBranchInventory\Models\Branch::getMainBranch();
                                                    @endphp
                                                    @if($currentMain && $branch->id != $currentMain->id)
                                                        <br><strong class="text-warning">⚠️ Warning: Main branch "{{ $currentMain->name }}" will be replaced</strong>
                                                    @elseif($branch->is_main_branch)
                                                        <br><strong class="text-success">✅ This is currently the main branch</strong>
                                                    @endif
                                                </span>
                                            </div>

                                            <!-- Branch Features -->
                                            <div class="form-group mb-3">
                                                <label class="control-label">Branch Features</label>
                                                <div class="checkbox-list">
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" name="features[]" value="pos_enabled" 
                                                               {{ in_array('pos_enabled', old('features', $branch->features ?? [])) ? 'checked' : '' }}>
                                                        POS Enabled
                                                    </label>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" name="features[]" value="online_orders" 
                                                               {{ in_array('online_orders', old('features', $branch->features ?? [])) ? 'checked' : '' }}>
                                                        Online Orders
                                                    </label>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" name="features[]" value="pickup_point" 
                                                               {{ in_array('pickup_point', old('features', $branch->features ?? [])) ? 'checked' : '' }}>
                                                        Pickup Point
                                                    </label>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" name="features[]" value="returns_accepted" 
                                                               {{ in_array('returns_accepted', old('features', $branch->features ?? [])) ? 'checked' : '' }}>
                                                        Returns Accepted
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Branch Statistics (if editing) -->
                                    @if($branch->exists)
                                    <div class="widget meta-boxes">
                                        <div class="widget-title">
                                            <h4><span>Branch Statistics</span></h4>
                                        </div>
                                        <div class="widget-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <tr>
                                                        <td>Total Products:</td>
                                                        <td><strong>{{ $branch->inventoryItems()->count() }}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Total Stock:</td>
                                                        <td><strong>{{ $branch->inventoryItems()->sum('quantity_on_hand') }}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Low Stock Items:</td>
                                                        <td><strong class="text-warning">{{ $branch->inventoryItems()->whereRaw('quantity_available <= minimum_stock')->count() }}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Created</td>
                                                        <td>{{ $branch->created_at->format('Y-m-d') }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainBranchCheckbox = document.getElementById('is_main_branch');
    const currentMainBranch = @json($currentMain && $branch->id != $currentMain->id ? $currentMain->name : null);
    const isCurrentlyMain = {{ $branch->is_main_branch ? 'true' : 'false' }};
    
    if (mainBranchCheckbox && currentMainBranch) {
        mainBranchCheckbox.addEventListener('change', function() {
            if (this.checked && !isCurrentlyMain) {
                const confirmed = confirm(
                    `⚠️ WARNING: Setting this branch as main will remove the main branch status from "${currentMainBranch}".\n\n` +
                    `Only ONE branch can be the main branch at a time.\n\n` +
                    `Are you sure you want to continue?`
                );
                
                if (!confirmed) {
                    this.checked = false;
                }
            }
        });
    }
});
</script>
@endpush
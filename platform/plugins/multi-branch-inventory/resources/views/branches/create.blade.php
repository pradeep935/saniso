@extends(BaseHelper::getAdminMasterLayoutTemplate())

@push('header')
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/base.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/plugins/multi-branch-inventory/css/branches.css') }}">
@endpush

@section('content')
    <div class="page-content">
        <div class="page-header">
            <h1>{{ isset($branch) ? trans('Edit Branch') : trans('Create Branch') }}</h1>
            <a href="{{ route('branches.index') }}" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> {{ trans('Back to Branches') }}
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-building"></i>
                            <span class="caption-subject">{{ trans('Branch Information') }}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <form method="POST" action="{{ isset($branch) ? route('branches.update', $branch->id) : route('branches.store') }}">
                            @csrf
                            @if(isset($branch))
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="required">{{ trans('Branch Name') }}</label>
                                        <input type="text" name="name" id="name" class="form-control" 
                                               value="{{ old('name', $branch->name ?? '') }}" required>
                                        @error('name')
                                            <span class="help-block text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="code" class="required">{{ trans('Branch Code') }}</label>
                                        <input type="text" name="code" id="code" class="form-control" 
                                               value="{{ old('code', $branch->code ?? '') }}" required
                                               placeholder="e.g., MAIN, SUB1">
                                        @error('code')
                                            <span class="help-block text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="address">{{ trans('Address') }}</label>
                                <textarea name="address" id="address" class="form-control" rows="3" 
                                          placeholder="Enter complete branch address...">{{ old('address', $branch->address ?? '') }}</textarea>
                                @error('address')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">{{ trans('Phone Number') }}</label>
                                        <input type="tel" name="phone" id="phone" class="form-control" 
                                               value="{{ old('phone', $branch->phone ?? '') }}"
                                               placeholder="+1 (555) 123-4567">
                                        @error('phone')
                                            <span class="help-block text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">{{ trans('Email Address') }}</label>
                                        <input type="email" name="email" id="email" class="form-control" 
                                               value="{{ old('email', $branch->email ?? '') }}"
                                               placeholder="branch@company.com">
                                        @error('email')
                                            <span class="help-block text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="manager_name">{{ trans('Manager Name') }}</label>
                                        <input type="text" name="manager_name" id="manager_name" class="form-control" 
                                               value="{{ old('manager_name', $branch->manager_name ?? '') }}"
                                               placeholder="Branch Manager Full Name">
                                        @error('manager_name')
                                            <span class="help-block text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="timezone">{{ trans('Timezone') }}</label>
                                        <select name="timezone" id="timezone" class="form-control">
                                            <option value="">{{ trans('Select Timezone') }}</option>
                                            <option value="America/New_York" {{ old('timezone', $branch->timezone ?? '') == 'America/New_York' ? 'selected' : '' }}>Eastern Time (ET)</option>
                                            <option value="America/Chicago" {{ old('timezone', $branch->timezone ?? '') == 'America/Chicago' ? 'selected' : '' }}>Central Time (CT)</option>
                                            <option value="America/Denver" {{ old('timezone', $branch->timezone ?? '') == 'America/Denver' ? 'selected' : '' }}>Mountain Time (MT)</option>
                                            <option value="America/Los_Angeles" {{ old('timezone', $branch->timezone ?? '') == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time (PT)</option>
                                            <option value="UTC" {{ old('timezone', $branch->timezone ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                        </select>
                                        @error('timezone')
                                            <span class="help-block text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="operating_hours">{{ trans('Operating Hours') }}</label>
                                <textarea name="operating_hours" id="operating_hours" class="form-control" rows="3"
                                          placeholder="e.g., Monday - Friday: 9:00 AM - 6:00 PM, Saturday: 10:00 AM - 4:00 PM">{{ old('operating_hours', $branch->operating_hours ?? '') }}</textarea>
                                @error('operating_hours')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="is_main_branch" value="1" 
                                                       id="is_main_branch"
                                                       {{ old('is_main_branch', $branch->is_main_branch ?? false) ? 'checked' : '' }}>
                                                {{ trans('Main Branch') }} 👑
                                            </label>
                                            <small class="help-block">
                                                {{ trans('Check if this is the main/headquarters branch') }}
                                                @php
                                                    $currentMain = \Botble\MultiBranchInventory\Models\Branch::getMainBranch();
                                                @endphp
                                                @if($currentMain && (!isset($branch) || $branch->id != $currentMain->id))
                                                    <br><strong class="text-warning">⚠️ Warning: Main branch "{{ $currentMain->name }}" will be replaced</strong>
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="has_pos" value="1" 
                                                       {{ old('has_pos', $branch->has_pos ?? false) ? 'checked' : '' }}>
                                                {{ trans('Has POS System') }}
                                            </label>
                                            <small class="help-block">{{ trans('Check if this branch has a POS system') }}</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="status" value="active" 
                                                       {{ old('status', $branch->status ?? 'active') == 'active' ? 'checked' : '' }}>
                                                {{ trans('Active') }}
                                            </label>
                                            <small class="help-block">{{ trans('Uncheck to deactivate this branch') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-save"></i> {{ trans('Save Branch') }}
                                </button>
                                <a href="{{ route('branches.index') }}" class="btn btn-default">
                                    <i class="fa fa-times"></i> {{ trans('Cancel') }}
                                </a>
                                @if(isset($branch))
                                    <button type="button" class="btn btn-info" onclick="testBranchConnection()">
                                        <i class="fa fa-wifi"></i> {{ trans('Test Connection') }}
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                @if(isset($branch))
                    <div class="portlet light bordered">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-info-circle"></i>
                                <span class="caption-subject">{{ trans('Branch Statistics') }}</span>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="stat-item">
                                        <div class="stat-value">{{ $branch->inventory()->count() }}</div>
                                        <div class="stat-label">{{ trans('Products in Inventory') }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="stat-item">
                                        <div class="stat-value text-success">${{ number_format($branch->inventory()->sum(\DB::raw('quantity_on_hand * cost_price')), 0) }}</div>
                                        <div class="stat-label">{{ trans('Inventory Value') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="stat-item">
                                        <div class="stat-value text-warning">{{ $branch->inventory()->whereColumn('quantity_on_hand', '<=', 'minimum_stock')->count() }}</div>
                                        <div class="stat-label">{{ trans('Low Stock Items') }}</div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="action-buttons">
                                <a href="{{ route('branch-inventory.index', ['branch_id' => $branch->id]) }}" class="btn btn-block btn-info">
                                    <i class="fa fa-boxes"></i> {{ trans('View Inventory') }}
                                </a>
                                <a href="{{ route('stock-transfers.index', ['branch_id' => $branch->id]) }}" class="btn btn-block btn-primary">
                                    <i class="fa fa-exchange-alt"></i> {{ trans('View Transfers') }}
                                </a>
                                <a href="{{ route('incoming-goods.index', ['branch_id' => $branch->id]) }}" class="btn btn-block btn-success">
                                    <i class="fa fa-truck"></i> {{ trans('Incoming Goods') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-lightbulb"></i>
                            <span class="caption-subject">{{ trans('Tips') }}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <ul class="list-unstyled">
                            <li><i class="fa fa-check text-success"></i> {{ trans('Branch code should be unique and short') }}</li>
                            <li><i class="fa fa-check text-success"></i> {{ trans('Complete address helps with shipping') }}</li>
                            <li><i class="fa fa-check text-success"></i> {{ trans('Only one branch can be marked as main') }}</li>
                            <li><i class="fa fa-check text-success"></i> {{ trans('POS integration requires active status') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainBranchCheckbox = document.getElementById('is_main_branch');
    const currentMainBranch = @json($currentMain ? $currentMain->name : null);
    
    if (mainBranchCheckbox && currentMainBranch) {
        mainBranchCheckbox.addEventListener('change', function() {
            if (this.checked) {
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

@section('javascript')
    <script>
        // Auto-generate branch code from name
        $('#name').on('blur', function() {
            if (!$('#code').val()) {
                const code = $(this).val().toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 6);
                $('#code').val(code);
            }
        });

        // Format phone number
        $('#phone').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            if (value.length >= 10) {
                value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
                $(this).val(value);
            }
        });

        // Test branch connection (for existing branches)
        function testBranchConnection() {
            const branchId = {{ $branch->id ?? 'null' }};
            if (!branchId) return;

            $.post('/admin/branches/' + branchId + '/test-connection', {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    toastr.success('Branch connection test successful!');
                } else {
                    toastr.error('Branch connection test failed: ' + response.message);
                }
            })
            .fail(function() {
                toastr.error('Unable to test branch connection');
            });
        }

        // Form validation
        $('form').on('submit', function(e) {
            const name = $('#name').val().trim();
            const code = $('#code').val().trim();

            if (!name) {
                e.preventDefault();
                toastr.error('Branch name is required');
                $('#name').focus();
                return false;
            }

            if (!code) {
                e.preventDefault();
                toastr.error('Branch code is required');
                $('#code').focus();
                return false;
            }

            if (code.length < 2) {
                e.preventDefault();
                toastr.error('Branch code must be at least 2 characters');
                $('#code').focus();
                return false;
            }
        });
    </script>
@endsection
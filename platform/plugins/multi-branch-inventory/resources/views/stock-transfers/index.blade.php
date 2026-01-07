@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="page-content">
        <div class="page-header">
            <h1>{{ trans('Stock Transfers') }}</h1>
            <div class="page-header-actions">
                <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> {{ trans('New Transfer') }}
                </a>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <form method="GET" class="form-inline">
                    <div class="form-group mr-2">
                        <label for="status" class="mr-2">{{ trans('Status') }}:</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">{{ trans('All Statuses') }}</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                {{ trans('Pending') }}
                            </option>
                            <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>
                                {{ trans('In Transit') }}
                            </option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                {{ trans('Completed') }}
                            </option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                {{ trans('Cancelled') }}
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-group mr-2">
                        <label for="from_branch" class="mr-2">{{ trans('From Branch') }}:</label>
                        <select name="from_branch" id="from_branch" class="form-control">
                            <option value="">{{ trans('All Branches') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('from_branch') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group mr-2">
                        <label for="to_branch" class="mr-2">{{ trans('To Branch') }}:</label>
                        <select name="to_branch" id="to_branch" class="form-control">
                            <option value="">{{ trans('All Branches') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('to_branch') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group mr-2">
                        <label for="date_from" class="mr-2">{{ trans('From Date') }}:</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" 
                               value="{{ request('date_from') }}">
                    </div>
                    
                    <div class="form-group mr-2">
                        <label for="date_to" class="mr-2">{{ trans('To Date') }}:</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" 
                               value="{{ request('date_to') }}">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> {{ trans('Filter') }}
                    </button>
                    <a href="{{ route('stock-transfers.index') }}" class="btn btn-default ml-2">
                        <i class="fa fa-refresh"></i> {{ trans('Reset') }}
                    </a>
                </form>
            </div>
        </div>

        <div class="portlet light bordered">
            <div class="portlet-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="transfers-table">
                        <thead>
                            <tr>
                                <th>{{ trans('Transfer ID') }}</th>
                                <th>{{ trans('From Branch') }}</th>
                                <th>{{ trans('To Branch') }}</th>
                                <th>{{ trans('Transfer Date') }}</th>
                                <th>{{ trans('Items') }}</th>
                                <th>{{ trans('Total Value') }}</th>
                                <th>{{ trans('Status') }}</th>
                                <th>{{ trans('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transfers as $transfer)
                                <tr>
                                    <td>
                                        <strong>#{{ $transfer->id }}</strong>
                                        @if($transfer->reference_number)
                                            <br><small class="text-muted">{{ $transfer->reference_number }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="label label-default">{{ $transfer->fromBranch->name }}</span>
                                        <br><small class="text-muted">{{ $transfer->fromBranch->code }}</small>
                                    </td>
                                    <td>
                                        <span class="label label-info">{{ $transfer->toBranch->name }}</span>
                                        <br><small class="text-muted">{{ $transfer->toBranch->code }}</small>
                                    </td>
                                    <td>
                                        {{ $transfer->transfer_date->format('M d, Y') }}
                                        <br><small class="text-muted">{{ $transfer->transfer_date->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $transfer->items->count() }}</span> items
                                        <br><small class="text-muted">{{ $transfer->items->sum('quantity') }} units</small>
                                    </td>
                                    <td>
                                        ${{ number_format($transfer->items->sum(function($item) { 
                                            return $item->quantity * $item->unit_cost; 
                                        }), 2) }}
                                    </td>
                                    <td>
                                        @switch($transfer->status)
                                            @case('pending')
                                                <span class="label label-warning">{{ trans('Pending') }}</span>
                                                @break
                                            @case('in_transit')
                                                <span class="label label-info">{{ trans('In Transit') }}</span>
                                                @break
                                            @case('completed')
                                                <span class="label label-success">{{ trans('Completed') }}</span>
                                                @break
                                            @case('cancelled')
                                                <span class="label label-danger">{{ trans('Cancelled') }}</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('stock-transfers.show', $transfer->id) }}" 
                                               class="btn btn-xs btn-info" title="{{ trans('View Details') }}">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            
                                            @if($transfer->status === 'pending')
                                                <a href="{{ route('stock-transfers.edit', $transfer->id) }}" 
                                                   class="btn btn-xs btn-warning" title="{{ trans('Edit') }}">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-xs btn-success" 
                                                        onclick="updateStatus({{ $transfer->id }}, 'in_transit')"
                                                        title="{{ trans('Mark In Transit') }}">
                                                    <i class="fa fa-truck"></i>
                                                </button>
                                            @endif
                                            
                                            @if($transfer->status === 'in_transit')
                                                <button type="button" class="btn btn-xs btn-success" 
                                                        onclick="updateStatus({{ $transfer->id }}, 'completed')"
                                                        title="{{ trans('Mark Completed') }}">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            @endif
                                            
                                            @if(in_array($transfer->status, ['pending', 'in_transit']))
                                                <button type="button" class="btn btn-xs btn-danger" 
                                                        onclick="updateStatus({{ $transfer->id }}, 'cancelled')"
                                                        title="{{ trans('Cancel Transfer') }}">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            @endif
                                            
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-xs btn-default dropdown-toggle" 
                                                        data-toggle="dropdown">
                                                    <i class="fa fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="#" onclick="printTransfer({{ $transfer->id }})">
                                                        <i class="fa fa-print"></i> {{ trans('Print') }}
                                                    </a></li>
                                                    <li><a href="#" onclick="emailTransfer({{ $transfer->id }})">
                                                        <i class="fa fa-envelope"></i> {{ trans('Email') }}
                                                    </a></li>
                                                    <li><a href="{{ route('stock-transfers.duplicate', $transfer->id) }}">
                                                        <i class="fa fa-copy"></i> {{ trans('Duplicate') }}
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="empty-state">
                                            <i class="fa fa-exchange-alt fa-3x text-muted"></i>
                                            <h4 class="text-muted">{{ trans('No stock transfers found') }}</h4>
                                            <p class="text-muted">{{ trans('Create your first stock transfer to move inventory between branches') }}</p>
                                            <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary">
                                                <i class="fa fa-plus"></i> {{ trans('Create Transfer') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transfers->hasPages())
                    <div class="row">
                        <div class="col-md-12">
                            {{ $transfers->appends(request()->query())->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusUpdateModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <form id="statusUpdateForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h4 class="modal-title">{{ trans('Update Transfer Status') }}</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>{{ trans('Are you sure you want to update the transfer status?') }}</p>
                        <div class="form-group">
                            <label for="statusReason">{{ trans('Reason (Optional)') }}:</label>
                            <textarea name="reason" id="statusReason" class="form-control" rows="3" 
                                      placeholder="Enter reason for status change..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ trans('Update Status') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        function updateStatus(transferId, status) {
            $('#statusUpdateForm').attr('action', '/admin/stock-transfers/' + transferId + '/status');
            $('#statusUpdateForm input[name="status"]').remove();
            $('#statusUpdateForm').append('<input type="hidden" name="status" value="' + status + '">');
            $('#statusUpdateModal').modal('show');
        }

        function printTransfer(transferId) {
            window.open('/admin/stock-transfers/' + transferId + '/print', '_blank');
        }

        function emailTransfer(transferId) {
            // Implementation for emailing transfer details
            toastr.info('Email functionality will be implemented');
        }

        // Auto-submit filters on change
        $('#status, #from_branch, #to_branch').on('change', function() {
            $(this).closest('form').submit();
        });

        // Date range validation
        $('#date_from, #date_to').on('change', function() {
            const dateFrom = new Date($('#date_from').val());
            const dateTo = new Date($('#date_to').val());
            
            if (dateFrom && dateTo && dateFrom > dateTo) {
                toastr.error('From date cannot be later than to date');
                $(this).val('');
            }
        });

        // Status update form submission
        $('#statusUpdateForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'PATCH',
                data: $(this).serialize(),
                success: function(response) {
                    $('#statusUpdateModal').modal('hide');
                    toastr.success('Transfer status updated successfully');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    toastr.error('Error updating transfer status');
                }
            });
        });

        // Initialize DataTable if available
        if ($.fn.DataTable) {
            $('#transfers-table').DataTable({
                responsive: true,
                order: [[0, 'desc']],
                pageLength: 25,
                columns: [
                    null,
                    null,
                    null,
                    { type: 'date' },
                    null,
                    { type: 'num-fmt' },
                    null,
                    { orderable: false }
                ]
            });
        }
    </script>
@endsection
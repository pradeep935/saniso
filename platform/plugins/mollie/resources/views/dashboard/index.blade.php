@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="ti ti-credit-card me-2"></i>
                        {{ trans('Mollie Payment Dashboard') }}
                    </h4>
                    <div class="card-tools">
                        <button class="btn btn-primary btn-sm" onclick="refreshDashboard()">
                            <i class="ti ti-refresh me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-white mb-0">€{{ $stats['amounts']['today'] }}</h4>
                            <p class="text-white-50 mb-0">Today's Revenue</p>
                        </div>
                        <div class="align-self-center">
                            <i class="ti ti-currency-euro h1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-white mb-0">{{ $stats['orders']['today'] }}</h4>
                            <p class="text-white-50 mb-0">Today's Orders</p>
                        </div>
                        <div class="align-self-center">
                            <i class="ti ti-shopping-cart h1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-white mb-0">{{ $terminalStatus['count'] }}</h4>
                            <p class="text-white-50 mb-0">Active Terminals</p>
                        </div>
                        <div class="align-self-center">
                            <i class="ti ti-device-mobile h1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="text-white mb-0">€{{ $stats['amounts']['week'] }}</h4>
                            <p class="text-white-50 mb-0">This Week</p>
                        </div>
                        <div class="align-self-center">
                            <i class="ti ti-calendar h1 text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Filters and Analytics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Revenue Analytics</h5>
                    <div class="card-tools">
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-primary btn-sm period-filter active" data-period="today">Today</button>
                            <button class="btn btn-outline-primary btn-sm period-filter" data-period="yesterday">Yesterday</button>
                            <button class="btn btn-outline-primary btn-sm period-filter" data-period="week">This Week</button>
                            <button class="btn btn-outline-primary btn-sm period-filter" data-period="month">This Month</button>
                        </div>
                        <div class="btn-group ms-2" role="group">
                            <button class="btn btn-outline-secondary btn-sm type-filter active" data-type="all">All</button>
                            <button class="btn btn-outline-secondary btn-sm type-filter" data-type="terminal">Terminal</button>
                            <button class="btn btn-outline-secondary btn-sm type-filter" data-type="webshop">Webshop</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Analytics Summary -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 id="analytics-amount" class="text-primary">€{{ $stats['amounts']['today'] }}</h4>
                                <p class="text-muted">Total Amount</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 id="analytics-orders" class="text-success">{{ $stats['orders']['today'] }}</h4>
                                <p class="text-muted">Total Orders</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 id="analytics-average" class="text-info">
                                    €{{ $stats['orders']['today'] > 0 ? number_format(floatval(str_replace(',', '', $stats['amounts']['today'])) / $stats['orders']['today'], 2) : '0.00' }}
                                </h4>
                                <p class="text-muted">Average Order</p>
                            </div>
                        </div>
                    </div>

                    <!-- Charts would go here -->
                    <div class="analytics-chart">
                        <canvas id="revenueChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Split View: Terminal vs Webshop -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-device-mobile me-2"></i>
                        Terminal Payments (Today)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h3 class="text-primary">€{{ $stats['amounts']['terminal_today'] }}</h3>
                            <p class="text-muted mb-0">Amount</p>
                        </div>
                        <div class="col-6">
                            <h3 class="text-primary">{{ $stats['orders']['terminal_today'] }}</h3>
                            <p class="text-muted mb-0">Orders</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-world-www me-2"></i>
                        Webshop Payments (Today)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h3 class="text-success">€{{ $stats['amounts']['webshop_today'] }}</h3>
                            <p class="text-muted mb-0">Amount</p>
                        </div>
                        <div class="col-6">
                            <h3 class="text-success">{{ $stats['orders']['webshop_today'] }}</h3>
                            <p class="text-muted mb-0">Orders</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Status Breakdown -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Payment Status (Today)</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="p-3 rounded bg-success-subtle">
                                <h4 class="text-success">{{ $stats['payments']['successful'] }}</h4>
                                <p class="text-muted mb-0">Successful</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded bg-warning-subtle">
                                <h4 class="text-warning">{{ $stats['payments']['pending'] }}</h4>
                                <p class="text-muted mb-0">Pending</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded bg-danger-subtle">
                                <h4 class="text-danger">{{ $stats['payments']['failed'] }}</h4>
                                <p class="text-muted mb-0">Failed</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded bg-info-subtle">
                                <h4 class="text-info">{{ $stats['payments']['refunded'] }}</h4>
                                <p class="text-muted mb-0">Refunded</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terminal Status -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="ti ti-device-mobile me-2"></i>
                        Terminal Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            @if($terminalStatus['active'])
                                <i class="ti ti-circle-check h2 text-success mb-0"></i>
                            @else
                                <i class="ti ti-circle-x h2 text-danger mb-0"></i>
                            @endif
                        </div>
                        <div>
                            <h5 class="mb-1">
                                @if($terminalStatus['active'])
                                    Connected
                                @else
                                    Disconnected
                                @endif
                            </h5>
                            <p class="text-muted mb-0">API Status: {{ ucfirst($terminalStatus['api_status']) }}</p>
                            @if(isset($terminalStatus['error']))
                                <small class="text-danger">{{ $terminalStatus['error'] }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="ti ti-plus me-2"></i>
                        Manual Terminal Payment
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <input type="number" class="form-control" id="manual-amount" placeholder="Enter amount (€)" step="0.01">
                        </div>
                        <div class="col-4">
                            <button class="btn btn-primary w-100" onclick="processManualPayment()" {{ !$terminalStatus['active'] ? 'disabled' : '' }}>
                                <i class="ti ti-credit-card me-1"></i>
                                Pay
                            </button>
                        </div>
                    </div>
                    <small class="text-muted">Direct terminal payment without order</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Terminal Device Management Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-device-desktop me-2"></i>
                        Terminal Device Management
                    </h5>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addTerminalDevice()">
                        <i class="fas fa-plus me-1"></i> Add Terminal Device
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-3">Available Terminal Devices</h6>
                                <div id="terminal-devices-list">
                                    <div class="text-center p-3">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <div class="mt-2">Loading terminals...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-3">Pending Orders for Terminal Payment</h6>
                                <div id="pending-orders-list">
                                    <div class="text-center p-3">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <div class="mt-2">Loading orders...</div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-outline-primary btn-sm" onclick="refreshPendingOrders()">
                                        <i class="fas fa-sync-alt me-1"></i>
                                        <i class="ti ti-refresh me-1" style="display: none;"></i>
                                        Refresh Orders
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Orders</h5>
                    <div class="card-tools">
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-primary btn-sm order-filter active" data-source="all">All</button>
                            <button class="btn btn-outline-primary btn-sm order-filter" data-source="terminal">Terminal</button>
                            <button class="btn btn-outline-primary btn-sm order-filter" data-source="webshop">Webshop</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="orders-table">
                            <thead>
                                <tr>
                                    <th>Order Code</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Source</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                <tr data-source="{{ $order['source'] }}">
                                    <td><strong>{{ $order['code'] }}</strong></td>
                                    <td>{{ $order['customer_name'] }}</td>
                                    <td class="text-end"><strong>€{{ $order['amount'] }}</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $order['status_class'] ?? 'secondary' }} text-white">
                                            {{ ucfirst($order['status']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $order['payment_status_class'] ?? 'secondary' }} text-white">
                                            {{ ucfirst($order['payment_status']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $order['source'] === 'terminal' ? 'primary' : 'info' }} text-white">
                                            {{ ucfirst($order['source']) }}
                                        </span>
                                    </td>
                                    <td>{{ $order['created_at'] }}</td>
                                    <td>
                                        @if($order['can_pay'] ?? false)
                                            <button class="btn btn-sm btn-primary" onclick="sendToTerminal({{ $order['id'] }})" title="Send to Terminal">
                                                <i class="fas fa-credit-card me-1"></i>
                                                Pay
                                            </button>
                                        @else
                                            <span class="text-success small">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Paid
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Direct Mollie Payments -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-mobile-alt me-2"></i>
                        Direct Mollie Payments
                    </h5>
                    <small class="text-muted">
                        Payments received directly through Mollie terminal/app (NOT website orders)<br>
                        <em class="text-info">Website payments are shown in the "Recent Orders" section above</em>
                    </small>
                    <div class="card-tools mt-2">
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-secondary active" onclick="filterDirectPayments('all')" id="filter-all">
                                <i class="fas fa-list me-1"></i>All
                            </button>
                            <button class="btn btn-outline-success" onclick="filterDirectPayments('paid')" id="filter-paid">
                                <i class="fas fa-check me-1"></i>Paid
                            </button>
                            <button class="btn btn-outline-warning" onclick="filterDirectPayments('pending')" id="filter-pending">
                                <i class="fas fa-clock me-1"></i>Pending
                            </button>
                            <button class="btn btn-outline-danger" onclick="filterDirectPayments('failed')" id="filter-failed">
                                <i class="fas fa-times me-1"></i>Failed
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($directPayments) && count($directPayments) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" id="direct-payments-table">
                                <thead>
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Method</th>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Customer</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($directPayments as $payment)
                                    <tr data-status="{{ strtolower($payment['status']) }}" data-method="{{ strtolower($payment['method']) }}">
                                        <td>
                                            <small class="text-muted font-monospace">{{ $payment['id'] }}</small>
                                        </td>
                                        <td>
                                            <strong class="text-success">{{ $payment['amount'] }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge {{ $payment['status_class'] }}">
                                                @if($payment['status'] === 'paid')
                                                    <i class="fas fa-check me-1"></i>
                                                @elseif($payment['status'] === 'pending')
                                                    <i class="fas fa-clock me-1"></i>
                                                @elseif($payment['status'] === 'failed')
                                                    <i class="fas fa-times me-1"></i>
                                                @elseif($payment['status'] === 'canceled')
                                                    <i class="fas fa-ban me-1"></i>
                                                @endif
                                                {{ ucfirst($payment['status']) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($payment['method'] === 'pointofsale')
                                                <i class="fas fa-credit-card me-1 text-warning"></i>Terminal
                                            @elseif($payment['method'] === 'creditcard')
                                                <i class="fas fa-credit-card me-1 text-primary"></i>Credit Card
                                            @elseif($payment['method'] === 'ideal')
                                                <i class="fas fa-university me-1 text-info"></i>iDEAL
                                            @elseif($payment['method'] === 'bancontact')
                                                <i class="fas fa-credit-card me-1 text-success"></i>Bancontact
                                            @else
                                                <i class="fas fa-coins me-1 text-secondary"></i>{{ ucfirst($payment['method']) }}
                                            @endif
                                        </td>
                                        <td>
                                            <span title="{{ $payment['created_at'] }}">
                                                {{ $payment['created_at_human'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-truncate" style="max-width: 200px; display: inline-block;" title="{{ $payment['description'] ?? 'Terminal Payment' }}">
                                                {{ $payment['description'] ?? 'Terminal Payment' }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $payment['customer_name'] ?? 'Walk-in Customer' }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Simple pagination for direct payments -->
                        @if(count($directPayments) >= 10)
                        <div class="text-center mt-3">
                            <button class="btn btn-outline-primary btn-sm" onclick="loadMoreDirectPayments()">
                                <i class="fas fa-plus me-1"></i>Load More Payments
                            </button>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No direct Mollie terminal/app payments found.</p>
                            <small class="text-muted">
                                This section only shows payments made directly through Mollie terminal or app that are NOT tied to website orders.<br>
                                <strong>Website payments</strong> appear in the "Recent Orders" section above.
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

@push('header')
<style>
    /* Ensure FontAwesome icons are visible */
    .fas, .fa {
        display: inline-block !important;
        font-family: 'Font Awesome 5 Free', 'Font Awesome 6 Free', FontAwesome !important;
        font-weight: 900;
    }
    
    /* Terminal device cards */
    .terminal-device-card {
        transition: all 0.3s ease;
    }
    
    .terminal-device-card:hover {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }
    
    /* Status badges */
    .badge.bg-success {
        background-color: #198754 !important;
    }
    
    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
    }
    
    .badge.bg-secondary {
        background-color: #6c757d !important;
    }
    
    /* Button improvements */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .btn .fas, .btn .fa, .btn .ti {
        margin-right: 0.25rem;
    }
</style>
@endpush

@push('footer')
<script>
// Icon fallback handler
$(document).ready(function() {
    // Check if FontAwesome is loaded, if not try to show Tabler icons
    setTimeout(function() {
        if (!$('.fas').length || $('.fas').css('font-family').indexOf('Font Awesome') === -1) {
            console.log('FontAwesome not detected, showing Tabler icons as fallback');
            $('.ti').show();
            $('.fas, .fa').hide();
        } else {
            console.log('FontAwesome detected successfully');
        }
    }, 1000);
    
    // Period filter functionality
    $('.period-filter').click(function() {
        $('.period-filter').removeClass('active');
        $(this).addClass('active');
        loadAnalytics();
    });

    $('.type-filter').click(function() {
        $('.type-filter').removeClass('active');
        $(this).addClass('active');
        loadAnalytics();
    });

    $('.order-filter').click(function() {
        $('.order-filter').removeClass('active');
        $(this).addClass('active');
        filterOrders();
    });
    
    // Load terminal devices and pending orders on page load
    loadTerminalDevices();
    loadPendingOrders();
});

function refreshDashboard() {
    location.reload();
}

function loadAnalytics() {
    const period = $('.period-filter.active').data('period');
    const type = $('.type-filter.active').data('type');
    
    $.ajax({
        url: '{{ route('mollie.analytics') }}',
        data: { period, type },
        success: function(response) {
            if (response.data) {
                $('#analytics-amount').text('€' + response.data.totals.amount);
                $('#analytics-orders').text(response.data.totals.orders);
                const avg = response.data.totals.orders > 0 ? 
                    (parseFloat(response.data.totals.amount.replace(',', '')) / response.data.totals.orders).toFixed(2) : '0.00';
                $('#analytics-average').text('€' + avg);
            }
        }
    });
}

function filterOrders() {
    const source = $('.order-filter.active').data('source');
    
    if (source === 'all') {
        $('#orders-table tbody tr').show();
    } else {
        $('#orders-table tbody tr').hide();
        $('#orders-table tbody tr[data-source="' + source + '"]').show();
    }
}

function sendToTerminal(orderId) {
    if (!confirm('Send this order to terminal for payment?')) return;
    
    $.ajax({
        url: '{{ route('mollie.manual-payment') }}',
        method: 'POST',
        data: {
            order_id: orderId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.error) {
                alert('Error: ' + response.message);
            } else {
                alert('Payment sent to terminal successfully!');
                refreshDashboard();
            }
        },
        error: function() {
            alert('Failed to send payment to terminal');
        }
    });
}

function processManualPayment() {
    const amount = $('#manual-amount').val();
    if (!amount || amount <= 0) {
        alert('Please enter a valid amount');
        return;
    }
    
    if (!confirm(`Process manual payment of €${amount}?`)) return;
    
    $.ajax({
        url: '{{ route('mollie.manual-payment') }}',
        method: 'POST',
        data: {
            amount: amount,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.error) {
                alert('Error: ' + response.message);
            } else {
                alert('Manual payment sent to terminal successfully!');
                $('#manual-amount').val('');
            }
        },
        error: function() {
            alert('Failed to process manual payment');
        }
    });
}

// Terminal Device Management Functions
function loadTerminalDevices() {
    $.ajax({
        url: '{{ route('mollie.dashboard.terminals') }}',
        success: function(response) {
            const container = $('#terminal-devices-list');
            if (response.error) {
                container.html(`
                    <div class="alert alert-warning">
                        <i class="ti ti-exclamation-triangle me-1"></i> 
                        ${response.message || 'Unable to load terminals'}
                    </div>`);
                return;
            }

            if (!response.data || !response.data.length) {
                container.html(`
                    <div class="text-center text-muted p-3">
                        <i class="ti ti-device-desktop h1 mb-2"></i>
                        <div>No terminal devices registered</div>
                        <small>Click "Add Terminal Device" to register a new terminal</small>
                    </div>`);
                return;
            }

            container.html(response.data.map(terminal => `
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                    <div class="flex-grow-1">
                        <strong>${terminal.description || terminal.name || terminal.id}</strong>
                        <div class="small text-muted">
                            ID: ${terminal.id}<br>
                            Brand: ${terminal.brand || 'Unknown'} | Model: ${terminal.model || 'Unknown'}
                        </div>
                        <span class="badge bg-${terminal.status === 'active' ? 'success' : (terminal.status === 'inactive' ? 'warning' : 'secondary')} text-white">
                            ${(terminal.status || 'unknown').toUpperCase()}
                        </span>
                    </div>
                    <div class="d-flex gap-2">
                        ${terminal.status === 'active' ? `
                            <button class="btn btn-success btn-sm" onclick="testTerminal('${terminal.id}')" title="Test Terminal">
                                <i class="fas fa-mobile-alt" style="font-size: 14px;"></i>
                                <i class="ti ti-device-mobile" style="display: none;"></i>
                                <span class="d-none d-sm-inline ms-1">Test</span>
                            </button>
                        ` : ''}
                        <button class="btn btn-outline-danger btn-sm" onclick="removeTerminalDevice('${terminal.id}')" title="Remove Terminal">
                            <i class="fas fa-trash" style="font-size: 14px;"></i>
                            <i class="ti ti-trash" style="display: none;"></i>
                            <span class="d-none d-sm-inline ms-1">Remove</span>
                        </button>
                    </div>
                </div>
            `).join(''));
        },
        error: function() {
            $('#terminal-devices-list').html(`
                <div class="alert alert-danger">
                    <i class="ti ti-exclamation-circle me-1"></i> Failed to load terminals
                </div>`);
        }
    });
}

function loadPendingOrders() {
    $.ajax({
        url: '{{ route('mollie.dashboard.pending-orders') }}',
        success: function(response) {
            const container = $('#pending-orders-list');
            if (response.error) {
                container.html(`
                    <div class="alert alert-warning">
                        <i class="ti ti-exclamation-triangle me-1"></i> 
                        ${response.message || 'Unable to load orders'}
                    </div>`);
                return;
            }

            if (!response.data.orders || !response.data.orders.length) {
                container.html(`
                    <div class="text-center text-muted p-3">
                        <i class="ti ti-check-circle h1 mb-2"></i>
                        <div>No pending orders</div>
                        <small>All orders are paid or completed</small>
                    </div>`);
                return;
            }

            container.html(response.data.orders.slice(0, 5).map(order => `
                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                    <div>
                        <strong>${order.code}</strong>
                        <div class="small text-muted">${order.customer_name}</div>
                        <div class="small">€${order.amount} • ${order.created_at}</div>
                    </div>
                    ${order.can_pay_terminal ? `
                        <button class="btn btn-success btn-sm" onclick="processTerminalPayment(${order.id}, '${order.code}', ${order.amount})">
                            <i class="fas fa-credit-card me-1"></i>
                            Pay
                        </button>
                    ` : `
                        <span class="badge bg-success text-white">Paid</span>
                    `}
                </div>
            `).join(''));
        },
        error: function() {
            $('#pending-orders-list').html(`
                <div class="alert alert-danger">
                    <i class="ti ti-exclamation-circle me-1"></i> Failed to load orders
                </div>`);
        }
    });
}

function addTerminalDevice() {
    const terminalId = prompt('Enter Terminal ID:', '');
    if (!terminalId || !terminalId.trim()) {
        alert('Terminal ID is required');
        return;
    }

    const terminalName = prompt('Enter Terminal Name (optional):', 'Terminal Device');

    $.ajax({
        url: '{{ route('mollie.dashboard.add-terminal') }}',
        method: 'POST',
        data: {
            terminal_id: terminalId.trim(),
            terminal_name: terminalName ? terminalName.trim() : 'Terminal Device',
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.error) {
                alert('Error: ' + response.message);
            } else {
                alert('Terminal device added successfully!');
                loadTerminalDevices();
            }
        },
        error: function() {
            alert('Failed to add terminal device');
        }
    });
}

function removeTerminalDevice(terminalId) {
    if (!confirm(`Are you sure you want to remove terminal: ${terminalId}?`)) {
        return;
    }

    $.ajax({
        url: '{{ route('mollie.dashboard.remove-terminal') }}',
        method: 'POST',
        data: {
            terminal_id: terminalId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.error) {
                alert('Error: ' + response.message);
            } else {
                alert('Terminal device removed successfully!');
                loadTerminalDevices();
            }
        },
        error: function() {
            alert('Failed to remove terminal device');
        }
    });
}

function processTerminalPayment(orderId, orderCode, amount) {
    if (!confirm(`Process terminal payment for ${orderCode} (€${amount})?`)) {
        return;
    }

    $.ajax({
        url: '{{ route('mollie.pos.process') }}',
        method: 'POST',
        data: {
            order_id: orderId,
            payment_type: 'card',
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                alert(`Payment initiated for ${orderCode}. Payment ID: ${response.payment_id}`);
                loadPendingOrders();
                loadAnalytics();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Failed to process payment');
        }
    });
}

function refreshPendingOrders() {
    loadPendingOrders();
}

function testTerminal(terminalId) {
    if (!confirm(`Test terminal connection: ${terminalId}?`)) {
        return;
    }

    // Simple test - attempt to get terminal info
    $.ajax({
        url: '{{ route('mollie.dashboard.terminals') }}',
        success: function(response) {
            if (response.data) {
                const terminal = response.data.find(t => t.id === terminalId);
                if (terminal) {
                    alert(`Terminal Test Result:
ID: ${terminal.id}
Status: ${terminal.status}
Brand: ${terminal.brand || 'Unknown'}
Model: ${terminal.model || 'Unknown'}

${terminal.status === 'active' ? '✅ Terminal is ACTIVE and ready for payments' : '❌ Terminal is NOT ACTIVE'}`);
                } else {
                    alert('Terminal not found');
                }
            }
        },
        error: function() {
            alert('Failed to test terminal connection');
        }
    });
}

// Enhanced analytics loading with source filtering
function loadAnalytics() {
    const period = $('.period-filter.active').data('period');
    const type = $('.type-filter.active').data('type');
    
    $.ajax({
        url: '{{ route('mollie.analytics') }}',
        data: { period, type },
        success: function(response) {
            if (response.data) {
                // Update main totals
                $('#analytics-amount').text('€' + response.data.totals.amount);
                $('#analytics-orders').text(response.data.totals.orders);
                const avg = response.data.totals.orders > 0 ? 
                    (parseFloat(response.data.totals.amount.replace(',', '')) / response.data.totals.orders).toFixed(2) : '0.00';
                $('#analytics-average').text('€' + avg);
                
                // Update source-specific data if available
                if (response.data.terminal && response.data.webshop) {
                    updateSourceSpecificAnalytics(response.data);
                }
            }
        }
    });
}

function updateSourceSpecificAnalytics(data) {
    // Update terminal analytics
    if ($('#terminal-amount').length) {
        $('#terminal-amount').text('€' + (data.terminal.amount || '0.00'));
    }
    if ($('#terminal-orders').length) {
        $('#terminal-orders').text(data.terminal.orders || '0');
    }
    
    // Update webshop analytics  
    if ($('#webshop-amount').length) {
        $('#webshop-amount').text('€' + (data.webshop.amount || '0.00'));
    }
    if ($('#webshop-orders').length) {
        $('#webshop-orders').text(data.webshop.orders || '0');
    }
}

// Direct Payments Filtering Functions
function filterDirectPayments(status) {
    // Update active button
    $('.card-tools .btn').removeClass('active');
    $('#filter-' + status).addClass('active');
    
    // Show/hide rows based on status
    const table = $('#direct-payments-table tbody');
    const rows = table.find('tr');
    let visibleCount = 0;
    
    rows.each(function() {
        const row = $(this);
        const rowStatus = row.data('status');
        
        if (status === 'all' || rowStatus === status) {
            row.show();
            visibleCount++;
        } else {
            row.hide();
        }
    });
    
    // Update empty state
    updateDirectPaymentsEmptyState(visibleCount);
}

function updateDirectPaymentsEmptyState(visibleCount) {
    const tableContainer = $('#direct-payments-table').parent();
    let emptyState = tableContainer.next('.empty-state');
    
    if (visibleCount === 0) {
        if (emptyState.length === 0) {
            emptyState = $(`
                <div class="empty-state text-center py-4">
                    <i class="fas fa-filter fa-2x text-muted mb-2"></i>
                    <p class="text-muted">No payments found for the selected filter.</p>
                    <small class="text-muted">Try selecting a different status filter.</small>
                </div>
            `);
            tableContainer.after(emptyState);
        }
        tableContainer.hide();
        emptyState.show();
    } else {
        tableContainer.show();
        emptyState.hide();
    }
}

function loadMoreDirectPayments() {
    // This would load more payments via AJAX in a real implementation
    // For now, just show a message
    const button = event.target;
    $(button).html('<i class="fas fa-spinner fa-spin me-1"></i>Loading...');
    
    // Simulate loading delay
    setTimeout(function() {
        $(button).html('<i class="fas fa-check me-1"></i>All payments loaded');
        $(button).prop('disabled', true);
    }, 1000);
}
</script>
@endpush
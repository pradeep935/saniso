<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Terminal Dashboard - Saniso Store</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .pos-dashboard { padding: 20px; background: #f8f9fa; min-height: 100vh; }
        .terminal-card { background: white; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .order-card { background: white; border-radius: 10px; padding: 15px; margin-bottom: 10px; border-left: 4px solid #007bff; }
        .order-card.paid { border-left-color: #28a745; }
        .order-card.pending { border-left-color: #ffc107; }
        .amount-display { font-size: 1.5rem; font-weight: bold; color: #007bff; }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-processing { background: #cce5ff; color: #004085; }
        .terminal-selector { margin-bottom: 20px; }
        .payment-progress { display: none; }
        .refresh-btn { position: fixed; top: 20px; right: 20px; z-index: 1000; }
    </style>
</head>
<body>
    <div class="pos-dashboard">
        <div class="container-fluid">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="terminal-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2><i class="bi bi-credit-card"></i> POS Terminal Dashboard</h2>
                                <p class="text-muted mb-0">Select orders and send payments to Mollie terminal</p>
                            </div>
                            <div class="text-end">
                                <button class="btn btn-primary refresh-btn" onclick="refreshDashboard()">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="terminal-card text-center">
                        <h4 id="pending-orders-count">0</h4>
                        <p class="text-muted mb-0">Pending Orders</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="terminal-card text-center">
                        <h4 id="available-terminals-count">0</h4>
                        <p class="text-muted mb-0">Available Terminals</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="terminal-card text-center">
                        <h4 id="processed-today">0</h4>
                        <p class="text-muted mb-0">Processed Today</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="terminal-card text-center">
                        <h4 class="text-success" id="total-amount">€0.00</h4>
                        <p class="text-muted mb-0">Total Amount</p>
                    </div>
                </div>
            </div>

            <!-- Terminal Selector -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="terminal-card">
                        <div class="terminal-selector">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="terminal-select" class="form-label"><strong>Select Terminal Device:</strong></label>
                                    <select id="terminal-select" class="form-select">
                                        <option value="">Auto-select terminal</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="payment-type" class="form-label"><strong>Payment Type:</strong></label>
                                    <select id="payment-type" class="form-select">
                                        <option value="card">Card Payment</option>
                                        <option value="voucher">Voucher Payment</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Voucher categories (hidden by default) -->
                            <div class="row mt-3" id="voucher-options" style="display: none;">
                                <div class="col-12">
                                    <label class="form-label"><strong>Voucher Categories (select at least one):</strong></label>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="meal" id="voucher-meal">
                                                <label class="form-check-label" for="voucher-meal">
                                                    Meal Vouchers
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="eco" id="voucher-eco">
                                                <label class="form-check-label" for="voucher-eco">
                                                    Eco Vouchers
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="gift" id="voucher-gift">
                                                <label class="form-check-label" for="voucher-gift">
                                                    Gift Vouchers
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="sport_culture" id="voucher-sport">
                                                <label class="form-check-label" for="voucher-sport">
                                                    Sport & Culture
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders List -->
            <div class="row">
                <div class="col-12">
                    <div class="terminal-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4><i class="bi bi-list-ul"></i> Orders Ready for Payment</h4>
                            <div>
                                <button class="btn btn-outline-primary btn-sm" onclick="filterOrders('all')">All</button>
                                <button class="btn btn-outline-warning btn-sm" onclick="filterOrders('pending')">Pending</button>
                                <button class="btn btn-outline-success btn-sm" onclick="filterOrders('processing')">Processing</button>
                            </div>
                        </div>
                        
                        <div id="orders-container">
                            <!-- Orders will be loaded here -->
                        </div>

                        <!-- No orders message -->
                        <div id="no-orders" class="text-center py-5" style="display: none;">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h5 class="text-muted mt-3">No orders ready for payment</h5>
                            <p class="text-muted">All orders are either paid or canceled</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Progress Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terminal Payment Progress</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="payment-progress">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Processing...</span>
                        </div>
                        <h5 id="payment-status-text">Sending payment to terminal...</h5>
                        <p id="payment-details" class="text-muted"></p>
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 25%"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="cancel-payment-btn" class="btn btn-danger" style="display: none;">Cancel Payment</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let currentOrders = [];
        let currentFilter = 'all';
        let currentPaymentId = null;
        let paymentCheckInterval = null;

        // CSRF token setup
        document.querySelector('meta[name="csrf-token"]').setAttribute('content', '{{ csrf_token() }}');

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboard();
            
            // Auto-refresh every 30 seconds
            setInterval(refreshDashboard, 30000);
            
            // Payment type change handler
            document.getElementById('payment-type').addEventListener('change', function() {
                const voucherOptions = document.getElementById('voucher-options');
                if (this.value === 'voucher') {
                    voucherOptions.style.display = 'block';
                } else {
                    voucherOptions.style.display = 'none';
                    // Uncheck all voucher checkboxes when switching to card payment
                    document.querySelectorAll('#voucher-options input[type="checkbox"]').forEach(cb => cb.checked = false);
                }
            });
        });

        // Load dashboard data
        async function loadDashboard() {
            try {
                const response = await fetch('/admin/mollie/terminal/pos-dashboard', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'include'
                });
                
                // Check if response is redirecting to login
                if (response.redirected && response.url.includes('/login')) {
                    window.location.href = '/admin/login';
                    return;
                }
                
                if (!response.ok) {
                    const responseText = await response.text();
                    console.error('API Response:', responseText);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const responseText = await response.text();
                let data;
                
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    console.error('Response received:', responseText.substring(0, 500));
                    throw new Error('Server returned invalid JSON response. Check console for details.');
                }
                
                if (data.error) {
                    // Handle authentication errors specifically
                    if (data.message && data.message.includes('Unauthenticated')) {
                        showAlert('Session expired. Redirecting to login...', 'warning');
                        setTimeout(() => {
                            window.location.href = '/admin/login';
                        }, 1500);
                        return;
                    }
                    throw new Error(data.message || 'Failed to load dashboard');
                }
                
                updateStats(data.data.stats);
                loadTerminals(data.data.terminals);
                loadOrders(data.data.orders);
                
            } catch (error) {
                console.error('Dashboard loading failed:', error);
                
                // Check if it's an authentication error
                if (error.message && (
                    error.message.includes('Unauthenticated') || 
                    error.message.includes('login') || 
                    error.message.includes('401') || 
                    error.message.includes('403')
                )) {
                    showAlert('Authentication required. Redirecting to login...', 'warning');
                    setTimeout(() => {
                        window.location.href = '/admin/login';
                    }, 2000);
                    return;
                }
                
                showAlert('Error loading dashboard: ' + error.message, 'danger');
                
                // Show fallback message in the dashboard
                document.getElementById('no-orders').style.display = 'block';
                document.getElementById('no-orders').innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-exclamation-triangle display-1 text-warning"></i>
                        <h5 class="text-warning mt-3">Failed to Load Dashboard</h5>
                        <p class="text-muted">${error.message}</p>
                        <button class="btn btn-primary" onclick="loadDashboard()">Retry</button>
                    </div>
                `;
            }
        }

        // Update stats cards
        function updateStats(stats) {
            document.getElementById('pending-orders-count').textContent = stats.pending_orders || 0;
            document.getElementById('available-terminals-count').textContent = stats.available_terminals || 0;
            
            // Calculate total amount
            const totalAmount = currentOrders.reduce((sum, order) => sum + parseFloat(order.amount || 0), 0);
            document.getElementById('total-amount').textContent = '€' + totalAmount.toFixed(2);
        }

        // Load available terminals
        function loadTerminals(terminals) {
            const select = document.getElementById('terminal-select');
            select.innerHTML = '<option value="">Default Terminal</option>';
            
            terminals.forEach(terminal => {
                const option = document.createElement('option');
                option.value = terminal.id;
                option.textContent = `${terminal.brand} ${terminal.model} (${terminal.serial_number}) - ${terminal.status}`;
                select.appendChild(option);
            });
        }

        // Load orders
        function loadOrders(orders) {
            currentOrders = orders;
            renderOrders();
        }

        // Render orders based on current filter
        function renderOrders() {
            const container = document.getElementById('orders-container');
            const noOrders = document.getElementById('no-orders');
            
            let filteredOrders = currentOrders;
            
            if (currentFilter !== 'all') {
                filteredOrders = currentOrders.filter(order => {
                    if (currentFilter === 'pending') return order.payment_status === 'pending' || order.payment_status === 'not_paid';
                    if (currentFilter === 'processing') return order.payment_status === 'processing';
                    return true;
                });
            }
            
            if (filteredOrders.length === 0) {
                container.style.display = 'none';
                noOrders.style.display = 'block';
                return;
            }
            
            container.style.display = 'block';
            noOrders.style.display = 'none';
            
            container.innerHTML = filteredOrders.map(order => `
                <div class="order-card ${getOrderStatusClass(order.payment_status)}">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <h6 class="mb-1">Order #${order.code}</h6>
                            <small class="text-muted">${order.created_at}</small>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong>${order.customer_name}</strong></p>
                            <small class="text-muted">${order.customer_phone}</small>
                        </div>
                        <div class="col-md-2">
                            <div class="amount-display">€${parseFloat(order.amount).toFixed(2)}</div>
                        </div>
                        <div class="col-md-2">
                            <span class="status-badge ${getStatusClass(order.payment_status)}">
                                ${getStatusText(order.payment_status)}
                            </span>
                        </div>
                        <div class="col-md-2 text-end">
                            ${order.can_pay_terminal ? `
                                <button class="btn btn-primary btn-sm" onclick="sendToTerminal(${order.id}, '${order.code}', ${order.amount})">
                                    <i class="bi bi-credit-card"></i> Pay Now
                                </button>
                            ` : `
                                <button class="btn btn-success btn-sm" disabled>
                                    <i class="bi bi-check-circle"></i> Paid
                                </button>
                            `}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Send payment to terminal
        async function sendToTerminal(orderId, orderCode, amount) {
            const terminalId = document.getElementById('terminal-select').value;
            const paymentType = document.getElementById('payment-type').value;
            
            let voucherCategories = [];
            if (paymentType === 'voucher') {
                // Get selected voucher categories
                const checkboxes = document.querySelectorAll('#voucher-options input[type="checkbox"]:checked');
                voucherCategories = Array.from(checkboxes).map(cb => cb.value);
                
                if (voucherCategories.length === 0) {
                    showAlert('Please select at least one voucher category', 'warning');
                    return;
                }
            }
            
            try {
                // Show progress modal
                const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
                document.getElementById('payment-status-text').textContent = 'Sending payment to terminal...';
                document.getElementById('payment-details').textContent = `Order #${orderCode} - €${parseFloat(amount).toFixed(2)} (${paymentType === 'voucher' ? 'Voucher' : 'Card'} Payment)`;
                modal.show();
                
                const requestBody = {
                    order_id: orderId,
                    terminal_id: terminalId,
                    payment_type: paymentType
                };
                
                if (paymentType === 'voucher') {
                    requestBody.voucher_categories = voucherCategories;
                }
                
                const response = await fetch('/admin/mollie/terminal/send-payment', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'include',
                    body: JSON.stringify(requestBody)
                });
                
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.message || 'Failed to send payment to terminal');
                }
                
                currentPaymentId = data.data.payment_id;
                document.getElementById('payment-status-text').textContent = 'Payment sent! Customer can now pay on terminal.';
                document.getElementById('cancel-payment-btn').style.display = 'inline-block';
                
                // Start checking payment status
                startPaymentStatusCheck();
                
                // Update progress bar
                document.querySelector('.progress-bar').style.width = '75%';
                
                showAlert(`Payment sent to terminal successfully for Order #${orderCode}`, 'success');
                
            } catch (error) {
                console.error('Send to terminal error:', error);
                showAlert('Error sending payment to terminal: ' + error.message, 'danger');
                bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
            }
        }

        // Start checking payment status
        function startPaymentStatusCheck() {
            if (paymentCheckInterval) {
                clearInterval(paymentCheckInterval);
            }
            
            paymentCheckInterval = setInterval(async () => {
                try {
                    const response = await fetch(`/admin/mollie/terminal/payment-status?payment_id=${currentPaymentId}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'include'
                    });
                    const data = await response.json();
                    
                    if (data.data.is_paid) {
                        // Payment completed
                        document.getElementById('payment-status-text').textContent = 'Payment completed! Order automatically updated.';
                        document.querySelector('.progress-bar').style.width = '100%';
                        document.querySelector('.progress-bar').classList.remove('progress-bar-animated');
                        document.querySelector('.progress-bar').classList.add('bg-success');
                        
                        clearInterval(paymentCheckInterval);
                        setTimeout(() => {
                            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                            refreshDashboard();
                        }, 3000);
                        
                    } else if (data.data.is_canceled) {
                        // Payment canceled
                        document.getElementById('payment-status-text').textContent = 'Payment was canceled or failed.';
                        document.querySelector('.progress-bar').classList.add('bg-danger');
                        
                        clearInterval(paymentCheckInterval);
                        
                    }
                } catch (error) {
                    console.error('Payment status check error:', error);
                }
            }, 3000); // Check every 3 seconds
        }

        // Filter orders
        function filterOrders(filter) {
            currentFilter = filter;
            
            // Update button states
            document.querySelectorAll('.btn-outline-primary, .btn-outline-warning, .btn-outline-success').forEach(btn => {
                btn.classList.remove('btn-primary', 'btn-warning', 'btn-success');
                btn.classList.add('btn-outline-primary', 'btn-outline-warning', 'btn-outline-success');
            });
            
            event.target.classList.remove('btn-outline-primary', 'btn-outline-warning', 'btn-outline-success');
            if (filter === 'pending') event.target.classList.add('btn-warning');
            else if (filter === 'processing') event.target.classList.add('btn-success');
            else event.target.classList.add('btn-primary');
            
            renderOrders();
        }

        // Refresh dashboard
        function refreshDashboard() {
            loadDashboard();
        }

        // Helper functions
        function getOrderStatusClass(status) {
            if (status === 'completed') return 'paid';
            if (status === 'pending' || status === 'processing') return 'pending';
            return '';
        }

        function getStatusClass(status) {
            if (status === 'completed') return 'status-paid';
            if (status === 'pending' || status === 'processing') return 'status-processing';
            return 'status-pending';
        }

        function getStatusText(status) {
            switch (status) {
                case 'completed': return 'Paid';
                case 'pending': return 'Pending';
                case 'processing': return 'Processing';
                case 'not_paid': return 'Not Paid';
                default: return 'Unknown';
            }
        }

        function showAlert(message, type = 'info') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.left = '50%';
            alertDiv.style.transform = 'translateX(-50%)';
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 5000);
        }
    </script>
</body>
</html>
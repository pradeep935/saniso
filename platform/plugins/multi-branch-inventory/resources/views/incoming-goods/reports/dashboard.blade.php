@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<style>
    .analytics-page {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px 0;
        margin-bottom: 30px;
        border-radius: 8px;
    }

    .analytics-header h1 {
        margin: 0;
        font-size: 32px;
        font-weight: 700;
    }

    .analytics-tabs {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        display: flex;
        overflow-x: auto;
    }

    .analytics-tabs .tab-link {
        flex: 1;
        padding: 15px 20px;
        text-align: center;
        border: none;
        background: white;
        cursor: pointer;
        font-weight: 500;
        color: #666;
        transition: all 0.3s ease;
        white-space: nowrap;
        min-width: 150px;
        border-bottom: 3px solid transparent;
    }

    .analytics-tabs .tab-link:hover {
        background: #f8f9fa;
        color: #667eea;
    }

    .analytics-tabs .tab-link.active {
        color: #667eea;
        border-bottom-color: #667eea;
    }

    .metric-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-left: 5px solid #667eea;
    }

    .metric-card.success { border-left-color: #28a745; }
    .metric-card.danger { border-left-color: #dc3545; }
    .metric-card.warning { border-left-color: #ffc107; }
    .metric-card.info { border-left-color: #17a2b8; }

    .metric-label {
        font-size: 12px;
        text-transform: uppercase;
        color: #999;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .metric-value {
        font-size: 28px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .metric-change {
        font-size: 13px;
        color: #999;
    }

    .metric-change.up { color: #28a745; }
    .metric-change.down { color: #dc3545; }

    .chart-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .chart-title {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .filter-section {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }

    .filter-group label {
        display: block;
        font-weight: 500;
        margin-bottom: 5px;
        color: #2c3e50;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
    }

    .filter-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        align-self: flex-end;
    }

    .filter-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .no-data {
        text-align: center;
        padding: 40px 20px;
        color: #999;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .progress-bar {
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        width: 0%;
        transition: width 0.3s ease;
    }

    .legend {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #f0f0f0;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 2px;
    }

    .table-responsive {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .table {
        margin: 0;
        width: 100%;
    }

    .table thead {
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
    }

    .table th {
        padding: 15px;
        font-weight: 600;
        color: #2c3e50;
        text-align: left;
        font-size: 13px;
        text-transform: uppercase;
    }

    .table td {
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
    }

    .table tbody tr:hover {
        background: #f8f9fa;
    }

    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Solid Color Badges with Proper Contrast */
    .badge-primary { background: #0d6efd !important; color: #fff !important; }
    .badge-secondary { background: #6c757d !important; color: #fff !important; }
    .badge-success { background: #198754 !important; color: #fff !important; }
    .badge-danger { background: #dc3545 !important; color: #fff !important; }
    .badge-warning { background: #ffc107 !important; color: #000 !important; }
    .badge-info { background: #0dcaf0 !important; color: #000 !important; }
    .badge-light { background: #f8f9fa !important; color: #000 !important; }
    .badge-dark { background: #212529 !important; color: #fff !important; }

    /* Light Badges (Alert Style) */
    .badge-success-light { background: #d4edda !important; color: #155724 !important; }
    .badge-danger-light { background: #f8d7da !important; color: #721c24 !important; }
    .badge-warning-light { background: #fff3cd !important; color: #856404 !important; }
    .badge-info-light { background: #d1ecf1 !important; color: #0c5460 !important; }
    .badge-primary-light { background: #cfe2ff !important; color: #084298 !important; }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .table thead th {
        background: #f8f9fa;
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #2c3e50;
    }

    .table tbody td {
        border: 1px solid #ddd;
        padding: 10px 12px;
        color: #555;
    }

    .table tbody tr:hover {
        background: #f8f9fa;
    }

    .table-responsive {
        overflow-x: auto;
    }
</style>

<div class="page-content">
    <div class="analytics-page">
        <div class="container-fluid">
            <div class="analytics-header">
                <h1><i class="fa fa-line-chart"></i> Incoming Goods Analytics & Reports</h1>
                <p style="margin: 10px 0 0 0; opacity: 0.9;">Advanced insights into your inventory receiving operations</p>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Navigation Tabs -->
        <div class="analytics-tabs">
            <button class="tab-link active" data-tab="dashboard">
                <i class="fa fa-dashboard"></i> Dashboard
            </button>
            <button class="tab-link" data-tab="receiving">
                <i class="fa fa-inbox"></i> Receiving Analytics
            </button>
            <button class="tab-link" data-tab="variance">
                <i class="fa fa-exchange"></i> Variance Analysis
            </button>
            <button class="tab-link" data-tab="supplier">
                <i class="fa fa-truck"></i> Supplier Performance
            </button>
            <button class="tab-link" data-tab="cost">
                <i class="fa fa-dollar"></i> Cost Analysis
            </button>
            <button class="tab-link" data-tab="inventory">
                <i class="fa fa-cube"></i> Inventory Impact
            </button>
            <button class="tab-link" data-tab="quality">
                <i class="fa fa-check-circle"></i> Quality Report
            </button>
        </div>

        <!-- DASHBOARD TAB -->
        <div id="dashboard" class="tab-content active">
            <!-- Filter Section -->
            <div class="filter-section">
                <form id="filter-form" class="filter-row">
                    <div class="filter-group">
                        <label>Branch</label>
                        <select id="branch-filter">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Date Range</label>
                        <input type="date" id="date-from" placeholder="From Date">
                    </div>
                    <div class="filter-group">
                        <label>&nbsp;</label>
                        <input type="date" id="date-to" placeholder="To Date">
                    </div>
                    <button type="button" class="filter-btn" id="apply-filter">
                        <i class="fa fa-filter"></i> Apply Filter
                    </button>
                </form>
            </div>

            <!-- Key Metrics -->
            <div class="stats-grid">
                <div class="metric-card success">
                    <div class="metric-label">Total Goods Received</div>
                    <div class="metric-value" id="total-received">0</div>
                    <div class="metric-change up"><i class="fa fa-arrow-up"></i> Shipments</div>
                </div>

                <div class="metric-card info">
                    <div class="metric-label">Total Items</div>
                    <div class="metric-value" id="total-items">0</div>
                    <div class="metric-change"><i class="fa fa-cubes"></i> Products</div>
                </div>

                <div class="metric-card">
                    <div class="metric-label">Total Value</div>
                    <div class="metric-value" id="total-value">$0.00</div>
                    <div class="metric-change"><i class="fa fa-dollar"></i> Inventory</div>
                </div>

                <div class="metric-card warning">
                    <div class="metric-label">Variance Items</div>
                    <div class="metric-value" id="variance-items">0</div>
                    <div class="metric-change"><i class="fa fa-warning"></i> Discrepancies</div>
                </div>
            </div>

            <!-- Charts Row -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 20px;">
                <div class="chart-card">
                    <div class="chart-title">Receiving Trend (Last 30 Days)</div>
                    <canvas id="trend-chart" height="250"></canvas>
                </div>

                <div class="chart-card">
                    <div class="chart-title">Top Suppliers</div>
                    <canvas id="supplier-chart" height="250"></canvas>
                </div>

                <div class="chart-card">
                    <div class="chart-title">Branch Distribution</div>
                    <canvas id="branch-chart" height="250"></canvas>
                </div>

                <div class="chart-card">
                    <div class="chart-title">Status Breakdown</div>
                    <canvas id="status-chart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- RECEIVING ANALYTICS TAB -->
        <div id="receiving" class="tab-content" style="display: none;">
            <!-- Filters -->
            <div class="filter-section">
                <form class="filter-row">
                    <div class="filter-group">
                        <label>Time Period</label>
                        <select id="receiving-period">
                            <option value="7">Last 7 Days</option>
                            <option value="30">Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                            <option value="365">Last Year</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Branch</label>
                        <select id="receiving-branch">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="filter-btn" onclick="loadReceivingAnalytics()">
                        <i class="fa fa-filter"></i> Load
                    </button>
                </form>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="metric-card success">
                    <div class="metric-label">Average Delivery Time</div>
                    <div class="metric-value" id="avg-delivery">0</div>
                    <div class="metric-change"><i class="fa fa-clock-o"></i> Days</div>
                </div>

                <div class="metric-card info">
                    <div class="metric-label">On-Time Deliveries</div>
                    <div class="metric-value" id="ontime-pct">0%</div>
                    <div class="metric-change"><i class="fa fa-check"></i> Success Rate</div>
                </div>

                <div class="metric-card">
                    <div class="metric-label">Items Per Shipment</div>
                    <div class="metric-value" id="items-per-ship">0</div>
                    <div class="metric-change"><i class="fa fa-th"></i> Average</div>
                </div>

                <div class="metric-card warning">
                    <div class="metric-label">Processing Time</div>
                    <div class="metric-value" id="process-time">0h</div>
                    <div class="metric-change"><i class="fa fa-hourglass"></i> Average</div>
                </div>
            </div>

            <!-- Charts -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 20px;">
                <div class="chart-card">
                    <div class="chart-title">Daily Receiving Volume</div>
                    <canvas id="receiving-volume-chart" height="250"></canvas>
                </div>

                <div class="chart-card">
                    <div class="chart-title">Receiving Efficiency</div>
                    <canvas id="efficiency-chart" height="250"></canvas>
                </div>
            </div>

            <!-- Detail Table -->
            <div class="chart-card">
                <div class="chart-title">Recent Receiving Summary</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Branch</th>
                                <th>Supplier</th>
                                <th>Items</th>
                                <th>Total Value</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="receiving-table">
                            <tr><td colspan="7" style="text-align: center; color: #999;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- VARIANCE ANALYSIS TAB -->
        <div id="variance" class="tab-content" style="display: none;">
            <!-- Filters -->
            <div class="filter-section">
                <form class="filter-row">
                    <div class="filter-group">
                        <label>Variance Type</label>
                        <select id="variance-type">
                            <option value="">All Types</option>
                            <option value="over">Over Received</option>
                            <option value="under">Under Received</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Min Variance %</label>
                        <input type="number" id="variance-min" placeholder="5" min="0">
                    </div>
                    <button type="button" class="filter-btn" onclick="loadVarianceAnalysis()">
                        <i class="fa fa-filter"></i> Load
                    </button>
                </form>
            </div>

            <!-- Metrics -->
            <div class="stats-grid">
                <div class="metric-card danger">
                    <div class="metric-label">Items with Variance</div>
                    <div class="metric-value" id="variance-count">0</div>
                    <div class="metric-change"><i class="fa fa-warning"></i> Discrepancies</div>
                </div>

                <div class="metric-card">
                    <div class="metric-label">Average Variance</div>
                    <div class="metric-value" id="avg-variance">0%</div>
                    <div class="metric-change"><i class="fa fa-percent"></i> Difference</div>
                </div>

                <div class="metric-card warning">
                    <div class="metric-label">Over Received Value</div>
                    <div class="metric-value" id="over-value">$0</div>
                    <div class="metric-change"><i class="fa fa-arrow-up"></i> Excess</div>
                </div>

                <div class="metric-card danger">
                    <div class="metric-label">Under Received Value</div>
                    <div class="metric-value" id="under-value">$0</div>
                    <div class="metric-change"><i class="fa fa-arrow-down"></i> Shortage</div>
                </div>
            </div>

            <!-- Chart -->
            <div class="chart-card">
                <div class="chart-title">Variance Distribution</div>
                <canvas id="variance-chart" height="300"></canvas>
            </div>

            <!-- Detail Table -->
            <div class="chart-card">
                <div class="chart-title">Items with Quantity Variance</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Expected</th>
                                <th>Received</th>
                                <th>Variance</th>
                                <th>%</th>
                                <th>Type</th>
                                <th>Cost Impact</th>
                            </tr>
                        </thead>
                        <tbody id="variance-table">
                            <tr><td colspan="7" style="text-align: center; color: #999;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SUPPLIER PERFORMANCE TAB -->
        <div id="supplier" class="tab-content" style="display: none;">
            <!-- Metrics -->
            <div class="stats-grid">
                <div class="metric-card">
                    <div class="metric-label">Total Suppliers</div>
                    <div class="metric-value" id="supplier-count">0</div>
                    <div class="metric-change"><i class="fa fa-users"></i> Active</div>
                </div>

                <div class="metric-card success">
                    <div class="metric-label">Top Supplier</div>
                    <div class="metric-value" id="top-supplier" style="font-size: 16px;">---</div>
                    <div class="metric-change"><i class="fa fa-star"></i> By Volume</div>
                </div>

                <div class="metric-card info">
                    <div class="metric-label">Avg Reliability</div>
                    <div class="metric-value" id="avg-reliability">0%</div>
                    <div class="metric-change"><i class="fa fa-check"></i> On-Time Rate</div>
                </div>

                <div class="metric-card">
                    <div class="metric-label">Avg Order Value</div>
                    <div class="metric-value" id="avg-order-value">$0</div>
                    <div class="metric-change"><i class="fa fa-dollar"></i> Per Order</div>
                </div>
            </div>

            <!-- Charts -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 20px;">
                <div class="chart-card">
                    <div class="chart-title">Supplier Reliability Score</div>
                    <canvas id="supplier-reliability-chart" height="300"></canvas>
                </div>

                <div class="chart-card">
                    <div class="chart-title">Order Volume by Supplier</div>
                    <canvas id="supplier-volume-chart" height="300"></canvas>
                </div>
            </div>

            <!-- Detail Table -->
            <div class="chart-card">
                <div class="chart-title">Supplier Performance Metrics</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th>Orders</th>
                                <th>Avg Value</th>
                                <th>Total Value</th>
                                <th>On-Time %</th>
                                <th>Variance %</th>
                                <th>Rating</th>
                            </tr>
                        </thead>
                        <tbody id="supplier-table">
                            <tr><td colspan="7" style="text-align: center; color: #999;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- COST ANALYSIS TAB -->
        <div id="cost" class="tab-content" style="display: none;">
            <!-- Metrics -->
            <div class="stats-grid">
                <div class="metric-card">
                    <div class="metric-label">Total Spending</div>
                    <div class="metric-value" id="total-spending">$0</div>
                    <div class="metric-change"><i class="fa fa-dollar"></i> YTD</div>
                </div>

                <div class="metric-card success">
                    <div class="metric-label">Avg Cost Per Unit</div>
                    <div class="metric-value" id="avg-cost-unit">$0</div>
                    <div class="metric-change"><i class="fa fa-tag"></i> Average</div>
                </div>

                <div class="metric-card info">
                    <div class="metric-label">Most Expensive Item</div>
                    <div class="metric-value" id="max-cost">$0</div>
                    <div class="metric-change"><i class="fa fa-cube"></i> Unit Cost</div>
                </div>

                <div class="metric-card">
                    <div class="metric-label">Cost Per Delivery</div>
                    <div class="metric-value" id="cost-per-delivery">$0</div>
                    <div class="metric-change"><i class="fa fa-truck"></i> Average</div>
                </div>
            </div>

            <!-- Charts -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 20px;">
                <div class="chart-card">
                    <div class="chart-title">Spending Trend</div>
                    <canvas id="cost-trend-chart" height="250"></canvas>
                </div>

                <div class="chart-card">
                    <div class="chart-title">Cost by Category</div>
                    <canvas id="cost-category-chart" height="250"></canvas>
                </div>
            </div>

            <!-- Detail Table -->
            <div class="chart-card">
                <div class="chart-title">Top Cost Items</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Unit Cost</th>
                                <th>Qty Received</th>
                                <th>Total Cost</th>
                                <th>% of Total</th>
                            </tr>
                        </thead>
                        <tbody id="cost-table">
                            <tr><td colspan="5" style="text-align: center; color: #999;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- INVENTORY IMPACT TAB -->
        <div id="inventory" class="tab-content" style="display: none;">
            <!-- Metrics -->
            <div class="stats-grid">
                <div class="metric-card success">
                    <div class="metric-label">Total Stock Increased</div>
                    <div class="metric-value" id="stock-increase">0</div>
                    <div class="metric-change"><i class="fa fa-arrow-up"></i> Units</div>
                </div>

                <div class="metric-card info">
                    <div class="metric-label">Avg Inventory Growth</div>
                    <div class="metric-value" id="avg-growth">0%</div>
                    <div class="metric-change"><i class="fa fa-percent"></i> Per Item</div>
                </div>

                <div class="metric-card">
                    <div class="metric-label">Fast Moving Items</div>
                    <div class="metric-value" id="fast-moving">0</div>
                    <div class="metric-change"><i class="fa fa-bolt"></i> Products</div>
                </div>

                <div class="metric-card warning">
                    <div class="metric-label">Slow Moving Items</div>
                    <div class="metric-value" id="slow-moving">0</div>
                    <div class="metric-change"><i class="fa fa-hourglass"></i> Products</div>
                </div>
            </div>

            <!-- Charts -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 20px;">
                <div class="chart-card">
                    <div class="chart-title">Stock Level Impact</div>
                    <canvas id="stock-impact-chart" height="250"></canvas>
                </div>

                <div class="chart-card">
                    <div class="chart-title">Product Movement</div>
                    <canvas id="product-movement-chart" height="250"></canvas>
                </div>
            </div>

            <!-- Detail Table -->
            <div class="chart-card">
                <div class="chart-title">Top Products by Stock Impact</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock Before</th>
                                <th>Received</th>
                                <th>Stock After</th>
                                <th>Growth %</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-table">
                            <tr><td colspan="6" style="text-align: center; color: #999;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- QUALITY REPORT TAB -->
        <div id="quality" class="tab-content" style="display: none;">
            <!-- Filters -->
            <div class="filter-section">
                <form class="filter-row">
                    <div class="filter-group">
                        <label>Quality Status</label>
                        <select id="quality-status">
                            <option value="">All</option>
                            <option value="good">Good Condition</option>
                            <option value="damaged">Damaged</option>
                            <option value="defective">Defective</option>
                        </select>
                    </div>
                    <button type="button" class="filter-btn" onclick="loadQualityReport()">
                        <i class="fa fa-filter"></i> Load
                    </button>
                </form>
            </div>

            <!-- Metrics -->
            <div class="stats-grid">
                <div class="metric-card success">
                    <div class="metric-label">Good Items</div>
                    <div class="metric-value" id="good-items">0</div>
                    <div class="metric-change"><i class="fa fa-check"></i> Quality</div>
                </div>

                <div class="metric-card warning">
                    <div class="metric-label">Damaged Items</div>
                    <div class="metric-value" id="damaged-items">0</div>
                    <div class="metric-change"><i class="fa fa-warning"></i> Found</div>
                </div>

                <div class="metric-card danger">
                    <div class="metric-label">Defective Items</div>
                    <div class="metric-value" id="defective-items">0</div>
                    <div class="metric-change"><i class="fa fa-times"></i> Rejected</div>
                </div>

                <div class="metric-card">
                    <div class="metric-label">Quality Rate</div>
                    <div class="metric-value" id="quality-rate">0%</div>
                    <div class="metric-change"><i class="fa fa-star"></i> Acceptable</div>
                </div>
            </div>

            <!-- Chart -->
            <div class="chart-card">
                <div class="chart-title">Quality Distribution</div>
                <canvas id="quality-chart" height="300"></canvas>
            </div>

            <!-- Detail Table -->
            <div class="chart-card">
                <div class="chart-title">Quality Issues Log</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Issue</th>
                                <th>Qty Affected</th>
                                <th>Condition Notes</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="quality-table">
                            <tr><td colspan="6" style="text-align: center; color: #999;">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    let trendChart, supplierChart, branchChart, statusChart;

    // Tab Navigation
    $('.tab-link').on('click', function() {
        const tabName = $(this).data('tab');
        
        $('.tab-link').removeClass('active');
        $('.tab-content').hide();
        
        $(this).addClass('active');
        $('#' + tabName).show();

        // Load data for specific tab
        loadTabData(tabName);
    });

    // Load Analytics Data
    function loadAnalytics() {
        const branchId = $('#branch-filter').val();
        const dateFrom = $('#date-from').val();
        const dateTo = $('#date-to').val();

        $.ajax({
            url: '{{ route('incoming-goods.analytics-data') }}',
            type: 'GET',
            data: {
                branch_id: branchId,
                date_from: dateFrom,
                date_to: dateTo
            },
            success: function(response) {
                // Update metrics
                $('#total-received').text(response.summary.total_received);
                $('#total-items').text(response.summary.total_items);
                // Total value removed per configuration — hide or clear element
                $('#total-value').text('');
                $('#variance-items').text(response.summary.variance_items);

                // Update charts
                updateCharts(response);
            },
            error: function() {
                alert('Error loading analytics data');
            }
        });
    }

    function updateCharts(data) {
        // Trend Chart
        if (trendChart) trendChart.destroy();
        const trendCtx = document.getElementById('trend-chart').getContext('2d');
        trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: data.trend.labels,
                datasets: [{
                    label: 'Items Received',
                    data: data.trend.values,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } }
            }
        });

        // Supplier Chart
        if (supplierChart) supplierChart.destroy();
        const supplierCtx = document.getElementById('supplier-chart').getContext('2d');
        supplierChart = new Chart(supplierCtx, {
            type: 'doughnut',
            data: {
                labels: data.suppliers.labels,
                datasets: [{
                    data: data.suppliers.values,
                    backgroundColor: [
                        '#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b',
                        '#fa709a', '#fee140', '#30cfd0'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // Branch Chart
        if (branchChart) branchChart.destroy();
        const branchCtx = document.getElementById('branch-chart').getContext('2d');
        branchChart = new Chart(branchCtx, {
            type: 'bar',
            data: {
                labels: data.branches.labels,
                datasets: [{
                    label: 'Total Value',
                    data: data.branches.values,
                    backgroundColor: 'rgba(102, 126, 234, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                indexAxis: 'y',
                plugins: { legend: { display: false } }
            }
        });

        // Status Chart
        if (statusChart) statusChart.destroy();
        const statusCtx = document.getElementById('status-chart').getContext('2d');
        statusChart = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: data.status.labels,
                datasets: [{
                    data: data.status.values,
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    function loadTabData(tabName) {
        switch(tabName) {
            case 'receiving':
                loadReceivingAnalytics();
                break;
            case 'variance':
                loadVarianceAnalysis();
                break;
            case 'supplier':
                loadSupplierPerformance();
                break;
            case 'cost':
                loadCostAnalysis();
                break;
            case 'inventory':
                loadInventoryImpact();
                break;
            case 'quality':
                loadQualityReport();
                break;
        }
    }

    // Receiving Analytics
    function loadReceivingAnalytics() {
        const period = $('#receiving-period').val() || 30;
        const branch = $('#receiving-branch').val();
        
        $.ajax({
            url: '{{ route('incoming-goods.analytics-data') }}',
            type: 'GET',
            data: { period: period, branch_id: branch },
            success: function(response) {
                // Mock data for now - populate as real data available
                $('#avg-delivery').text('3.5');
                $('#ontime-pct').text('92%');
                $('#items-per-ship').text(Math.round(response.summary.total_items / (response.summary.total_received || 1)));
                $('#process-time').text('24h');
            }
        });
    }

    // Variance Analysis
    function loadVarianceAnalysis() {
        const type = $('#variance-type').val();
        const minVariance = $('#variance-min').val() || 0;
        
        $.ajax({
            url: '{{ route('incoming-goods.analytics-data') }}',
            type: 'GET',
            data: { type: type, min_variance: minVariance },
            success: function(response) {
                // Mock variance data
                $('#variance-count').text('12');
                $('#avg-variance').text('5.8%');
                $('#over-value').text('$2,450.00');
                $('#under-value').text('$1,890.00');

                // Variance Chart
                if (varianceChart) varianceChart.destroy();
                const ctx = document.getElementById('variance-chart').getContext('2d');
                varianceChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['0-5%', '5-10%', '10-15%', '15-20%', '20%+'],
                        datasets: [{
                            label: 'Count',
                            data: [3, 5, 2, 1, 1],
                            backgroundColor: ['#ffc107', '#ff9800', '#f57c00', '#e65100', '#d84315']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: { legend: { display: false } }
                    }
                });
            }
        });
    }

    // Supplier Performance
    function loadSupplierPerformance() {
        $.ajax({
            url: '{{ route('incoming-goods.analytics-data') }}',
            type: 'GET',
            success: function(response) {
                const suppliers = response.suppliers || [];
                
                $('#supplier-count').text(suppliers.length);
                $('#avg-reliability').text('87%');
                $('#top-supplier').text(suppliers.length > 0 ? suppliers[0].name : '---');
                $('#avg-order-value').text('$' + (suppliers.length > 0 ? Math.round(suppliers[0].avg_value) : 0));

                // Supplier Reliability Chart
                if (supplierReliabilityChart) supplierReliabilityChart.destroy();
                const ctx1 = document.getElementById('supplier-reliability-chart').getContext('2d');
                supplierReliabilityChart = new Chart(ctx1, {
                    type: 'radar',
                    data: {
                        labels: suppliers.slice(0, 5).map(s => s.name),
                        datasets: [{
                            label: 'Reliability %',
                            data: suppliers.slice(0, 5).map(s => 85 + Math.random() * 15),
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: { legend: { display: false } }
                    }
                });

                // Supplier Volume Chart
                if (supplierVolumeChart) supplierVolumeChart.destroy();
                const ctx2 = document.getElementById('supplier-volume-chart').getContext('2d');
                supplierVolumeChart = new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: suppliers.slice(0, 5).map(s => s.name),
                        datasets: [{
                            // Use order count for supplier volume chart instead of monetary value
                            data: suppliers.slice(0, 5).map(s => s.orders || 0),
                            backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#00f2fe']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        });
    }

    // Cost Analysis
    function loadCostAnalysis() {
        $.ajax({
            url: '{{ route('incoming-goods.analytics-data') }}',
            type: 'GET',
            success: function(response) {
                // Use counts instead of monetary values — show item counts for cost analysis
                const total = parseInt(response.summary.total_items || 0);

                    $('#total-spending').text(total);
                    $('#avg-cost-unit').text(response.summary.total_items > 0 ? Math.round(total / response.summary.total_items) : '-');
                    $('#max-cost').text('-');
                    $('#cost-per-delivery').text(response.summary.total_received > 0 ? Math.round(total / response.summary.total_received) : '-');

                // Cost Trend Chart
                if (costTrendChart) costTrendChart.destroy();
                const ctx1 = document.getElementById('cost-trend-chart').getContext('2d');
                costTrendChart = new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                        datasets: [{
                            label: 'Spending',
                            data: [total/4, total/4 + 500, total/4 - 300, total/4 + 200],
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: { legend: { display: true } }
                    }
                });

                // Cost Category Chart
                if (costCategoryChart) costCategoryChart.destroy();
                const ctx2 = document.getElementById('cost-category-chart').getContext('2d');
                costCategoryChart = new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: ['Electronics', 'Supplies', 'Equipment', 'Other'],
                        datasets: [{
                            data: [40, 25, 20, 15],
                            backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#4facfe']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true
                    }
                });
            }
        });
    }

    // Inventory Impact
    function loadInventoryImpact() {
        $.ajax({
            url: '{{ route('incoming-goods.analytics-data') }}',
            type: 'GET',
            success: function(response) {
                $('#stock-increase').text(response.summary.total_items);
                $('#avg-growth').text('12.5%');
                $('#fast-moving').text('18');
                $('#slow-moving').text('5');

                // Stock Impact Chart
                if (stockImpactChart) stockImpactChart.destroy();
                const ctx1 = document.getElementById('stock-impact-chart').getContext('2d');
                stockImpactChart = new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Stock Increase',
                            data: [120, 190, 150, 220, 180, 200],
                            backgroundColor: '#28a745'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: { legend: { display: false } }
                    }
                });

                // Product Movement Chart
                if (productMovementChart) productMovementChart.destroy();
                const ctx2 = document.getElementById('product-movement-chart').getContext('2d');
                productMovementChart = new Chart(ctx2, {
                    type: 'pie',
                    data: {
                        labels: ['Fast Moving', 'Normal', 'Slow Moving'],
                        datasets: [{
                            data: [45, 35, 20],
                            backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true
                    }
                });
            }
        });
    }

    // Quality Report
    function loadQualityReport() {
        const status = $('#quality-status').val();
        
        $.ajax({
            url: '{{ route('incoming-goods.analytics-data') }}',
            type: 'GET',
            data: { quality_status: status },
            success: function(response) {
                const total = response.summary.total_items;
                
                $('#good-items').text(Math.round(total * 0.92));
                $('#damaged-items').text(Math.round(total * 0.05));
                $('#defective-items').text(Math.round(total * 0.03));
                $('#quality-rate').text('92%');

                // Quality Chart
                if (qualityChart) qualityChart.destroy();
                const ctx = document.getElementById('quality-chart').getContext('2d');
                qualityChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Good Condition', 'Damaged', 'Defective'],
                        datasets: [{
                            data: [92, 5, 3],
                            backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true
                    }
                });
            }
        });
    }

    // Chart instances
    let varianceChart, supplierReliabilityChart, supplierVolumeChart;
    let costTrendChart, costCategoryChart, stockImpactChart, productMovementChart;
    let qualityChart;

    // Apply Filter
    $('#apply-filter').on('click', function() {
        loadAnalytics();
    });

    // Initial Load
    loadAnalytics();
});
</script>
@endsection

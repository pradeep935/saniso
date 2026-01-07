<!-- RECEIVING ANALYTICS TAB -->
<div id="receiving-detail" class="tab-content-detail" style="display: none;">
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
            <div class="metric-change"><i class="fa fa-average"></i> Average</div>
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
<div id="variance-detail" class="tab-content-detail" style="display: none;">
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
<div id="supplier-detail" class="tab-content-detail" style="display: none;">
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
            <div class="metric-change"><i class="fa fa-money"></i> Per Order</div>
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
<div id="cost-detail" class="tab-content-detail" style="display: none;">
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
<div id="inventory-detail" class="tab-content-detail" style="display: none;">
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
            <div class="metric-change"><i class="fa fa-turtle"></i> Products</div>
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
<div id="quality-detail" class="tab-content-detail" style="display: none;">
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

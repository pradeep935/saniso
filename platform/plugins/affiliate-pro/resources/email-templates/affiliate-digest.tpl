{{ header }}

<div class="bb-main-content">
    <table class="bb-box" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td class="bb-content bb-pb-0" align="center">
                    <table class="bb-icon bb-icon-lg bb-bg-blue" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td valign="middle" align="center">
                                    <img src="{{ 'chart' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h1 class="bb-text-center bb-m-0 bb-mt-md">Your Weekly Affiliate Performance</h1>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center">
                    <p class="h1">Hello, {{ customer_name }}!</p>
                    <p>Here's a summary of your affiliate performance for the past week.</p>
                </td>
            </tr>
            <tr>
                <td class="bb-content">
                    <table class="bb-table" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td><strong>Period:</strong></td>
                                <td>{{ period_start }} - {{ period_end }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Clicks:</strong></td>
                                <td>{{ total_clicks }}</td>
                            </tr>
                            <tr>
                                <td><strong>Conversion Rate:</strong></td>
                                <td>{{ conversion_rate }}%</td>
                            </tr>
                            <tr>
                                <td><strong>New Commissions:</strong></td>
                                <td>{{ new_commissions_count }}</td>
                            </tr>
                            <tr>
                                <td><strong>Earnings This Week:</strong></td>
                                <td>{{ earnings_this_week }}</td>
                            </tr>
                            <tr>
                                <td><strong>Current Balance:</strong></td>
                                <td>{{ current_balance }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="bb-content">
                    <h2 class="bb-text-center">Performance Highlights</h2>
                    
                    <div class="bb-card bb-card-success bb-mb-md">
                        <div class="bb-card-body">
                            <h3 class="bb-card-title">Top Performing Products</h3>
                            <ul>
                                {% for product in top_products %}
                                <li>{{ product.name }} - {{ product.commissions }} commissions</li>
                                {% endfor %}
                            </ul>
                        </div>
                    </div>
                    
                    <div class="bb-card bb-card-info bb-mb-md">
                        <div class="bb-card-body">
                            <h3 class="bb-card-title">Traffic Sources</h3>
                            <ul>
                                {% for source in traffic_sources %}
                                <li>{{ source.name }} - {{ source.percentage }}%</li>
                                {% endfor %}
                            </ul>
                        </div>
                    </div>
                    
                    {% if tips|length > 0 %}
                    <div class="bb-card bb-card-warning">
                        <div class="bb-card-body">
                            <h3 class="bb-card-title">Optimization Tips</h3>
                            <ul>
                                {% for tip in tips %}
                                <li>{{ tip }}</li>
                                {% endfor %}
                            </ul>
                        </div>
                    </div>
                    {% endif %}
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center bb-pt-0 bb-pb-xl">
                    <table class="bb-button bb-button-lg bb-button-primary" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td align="center">
                                    <a href="{{ affiliate_dashboard_url }}" target="_blank">View Full Report</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{ footer }}

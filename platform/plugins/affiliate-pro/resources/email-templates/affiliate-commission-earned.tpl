{{ header }}

<div class="bb-main-content">
    <table class="bb-box" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td class="bb-content bb-pb-0" align="center">
                    <table class="bb-icon bb-icon-lg bb-bg-green" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td valign="middle" align="center">
                                    <img src="{{ 'coin' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h1 class="bb-text-center bb-m-0 bb-mt-md">New Commission Earned</h1>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center">
                    <p class="h1">Great news, {{ customer_name }}!</p>
                    <p>You've earned a new commission from a referred sale.</p>
                </td>
            </tr>
            <tr>
                <td class="bb-content">
                    <table class="bb-table" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td class="bb-table-label">Commission Amount:</td>
                                <td class="bb-table-value"><strong>{{ commission_amount }}</strong></td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Order ID:</td>
                                <td class="bb-table-value">#{{ order_id }}</td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Order Total:</td>
                                <td class="bb-table-value">{{ order_total }}</td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Commission Date:</td>
                                <td class="bb-table-value">{{ commission_date }}</td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Status:</td>
                                <td class="bb-table-value">{{ commission_status }}</td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Current Balance:</td>
                                <td class="bb-table-value"><strong>{{ current_balance }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center bb-pt-0 bb-pb-xl">
                    <table class="bb-button bb-button-lg bb-button-primary" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td align="center">
                                    <a href="{{ affiliate_dashboard_url }}" target="_blank">View Your Commissions</a>
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

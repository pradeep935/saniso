{{ header }}

<div class="bb-main-content">
    <table class="bb-box" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td class="bb-content bb-pb-0" align="center">
                    <table class="bb-icon bb-icon-lg bb-bg-red" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td valign="middle" align="center">
                                    <img src="{{ 'x' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h1 class="bb-text-center bb-m-0 bb-mt-md">Withdrawal Request Status</h1>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center">
                    <p class="h1">Hello, {{ customer_name }}</p>
                    <p>We regret to inform you that your withdrawal request could not be processed at this time.</p>
                </td>
            </tr>
            <tr>
                <td class="bb-content">
                    <table class="bb-table" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td class="bb-table-label">Amount:</td>
                                <td class="bb-table-value"><strong>{{ withdrawal_amount }}</strong></td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Payment Method:</td>
                                <td class="bb-table-value">{{ withdrawal_method }}</td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Rejection Date:</td>
                                <td class="bb-table-value">{{ rejection_date }}</td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Reason:</td>
                                <td class="bb-table-value">{{ rejection_reason }}</td>
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
                <td class="bb-content bb-text-center">
                    <p>The requested amount has been returned to your affiliate balance. You can submit a new withdrawal request after addressing the issue mentioned above.</p>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center bb-pt-0 bb-pb-xl">
                    <table class="bb-button bb-button-lg bb-button-primary" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td align="center">
                                    <a href="{{ affiliate_dashboard_url }}" target="_blank">Go to Affiliate Dashboard</a>
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

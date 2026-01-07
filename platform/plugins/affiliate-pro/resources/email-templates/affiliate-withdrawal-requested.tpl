{{ header }}

<div class="bb-main-content">
    <table class="bb-box" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td class="bb-content bb-pb-0" align="center">
                    <table class="bb-icon bb-icon-lg bb-bg-orange" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td valign="middle" align="center">
                                    <img src="{{ 'cash' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h1 class="bb-text-center bb-m-0 bb-mt-md">Withdrawal Request</h1>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center">
                    <p class="h1">New Withdrawal Request</p>
                    <p>An affiliate has requested a withdrawal and is awaiting your approval.</p>
                </td>
            </tr>
            <tr>
                <td class="bb-content">
                    <table class="bb-table" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td class="bb-table-label">Affiliate Name:</td>
                                <td class="bb-table-value">{{ customer_name }}</td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Email Address:</td>
                                <td class="bb-table-value">{{ customer_email }}</td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Withdrawal Amount:</td>
                                <td class="bb-table-value"><strong>{{ withdrawal_amount }}</strong></td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Payment Method:</td>
                                <td class="bb-table-value">{{ withdrawal_method }}</td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Request Date:</td>
                                <td class="bb-table-value">{{ request_date }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center">
                    <p>Please review this withdrawal request and process it accordingly.</p>
                    <table class="bb-btn bb-btn-primary" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td align="center">
                                    <a href="{{ admin_dashboard_url }}" target="_blank">Process Withdrawal</a>
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

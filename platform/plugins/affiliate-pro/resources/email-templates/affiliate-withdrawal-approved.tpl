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
                                    <img src="{{ 'check' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h1 class="bb-text-center bb-m-0 bb-mt-md">Withdrawal Request Approved</h1>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center">
                    <p class="h1">Hello, {{ customer_name }}</p>
                    <p>Your withdrawal request has been approved and is being processed.</p>
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
                                <td class="bb-table-label">Processing Date:</td>
                                <td class="bb-table-value">{{ processing_date }}</td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Transaction ID:</td>
                                <td class="bb-table-value">{{ transaction_id }}</td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Remaining Balance:</td>
                                <td class="bb-table-value"><strong>{{ remaining_balance }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center">
                    <p>You should receive your payment according to the processing time of your selected payment method. This typically takes 3-5 business days, but may vary depending on your payment provider.</p>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center bb-pt-0 bb-pb-xl">
                    <table class="bb-button bb-button-lg bb-button-primary" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td align="center">
                                    <a href="{{ affiliate_dashboard_url }}" target="_blank">View Your Withdrawals</a>
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

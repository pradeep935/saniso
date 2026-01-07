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
                    <h1 class="bb-text-center bb-m-0 bb-mt-md">Affiliate Application Approved</h1>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center">
                    <p class="h1">Congratulations, {{ customer_name }}!</p>
                    <p>Your application to join our affiliate program has been approved. You can now start promoting our products and earning commissions.</p>
                </td>
            </tr>
            <tr>
                <td class="bb-content">
                    <table class="bb-table" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td class="bb-table-label">Your Affiliate Code:</td>
                                <td class="bb-table-value"><strong>{{ affiliate_code }}</strong></td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Commission Rate:</td>
                                <td class="bb-table-value">{{ commission_rate }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="bb-text-center">Use your affiliate code in your promotions to earn commissions on referred sales.</p>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center bb-pt-0 bb-pb-xl">
                    <table class="bb-button bb-button-lg bb-button-primary" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td align="center">
                                    <a href="{{ affiliate_dashboard_url }}" target="_blank">Access Your Affiliate Dashboard</a>
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

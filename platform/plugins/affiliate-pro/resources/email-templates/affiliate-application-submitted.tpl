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
                                    <img src="{{ 'user-plus' | icon_url }}" class="bb-va-middle" width="40" height="40" alt="Icon">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h1 class="bb-text-center bb-m-0 bb-mt-md">New Affiliate Application</h1>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center">
                    <p class="h1">New Application Received</p>
                    <p>A new affiliate application has been submitted and is awaiting your review.</p>
                </td>
            </tr>
            <tr>
                <td class="bb-content">
                    <table class="bb-table" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td class="bb-table-label">Applicant Name:</td>
                                <td class="bb-table-value">{{ customer_name }}</td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Email Address:</td>
                                <td class="bb-table-value">{{ customer_email }}</td>
                            </tr>
                            <tr>
                                <td class="bb-table-label">Application Date:</td>
                                <td class="bb-table-value">{{ application_date }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="bb-content bb-text-center">
                    <p>Please review this application and take appropriate action.</p>
                    <table class="bb-btn bb-btn-primary" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td align="center">
                                    <a href="{{ admin_dashboard_url }}" target="_blank">Review Application</a>
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

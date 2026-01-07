{{ header }}

<table width="100%">
    <tbody>
        <tr>
            <td class="wrapper" width="700" align="center">
                <table class="section" cellpadding="0" cellspacing="0" width="700">
                    <tr>
                        <td class="column" align="left">
                            <table>
                                <tbody>
                                <tr>
                                    <td align="left" style="padding: 20px 50px;">
                                        <p><strong>Hello {{ customer_name }},</strong></p>
                                        <br />
                                        <p>Good news! Your affiliate account has been reinstated and you can now access all affiliate features again.</p>
                                        <br />
                                        <p>Your access to the affiliate dashboard has been fully restored. You can now:</p>
                                        <ul>
                                            <li>View your commissions and earnings</li>
                                            <li>Generate affiliate links</li>
                                            <li>Request withdrawals</li>
                                            <li>Access promotional materials</li>
                                        </ul>
                                        <br />
                                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                            <tr>
                                                <td align="center" style="padding: 20px 0;">
                                                    <a href="{{ affiliate_dashboard_url }}" style="display: inline-block; padding: 15px 30px; background-color: #0ea5e9; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold;">
                                                        Access Affiliate Dashboard
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                        <br />
                                        <p>Please ensure you comply with our affiliate program terms and conditions to maintain your account in good standing.</p>
                                        <br />
                                        <p>Best regards,<br />{{ site_title }}</p>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </tbody>
</table>

{{ footer }}
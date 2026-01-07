<!doctype html>
<html {{ html_attributes }}>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ 'plugins/ecommerce::picking-list-template.pakbon'|trans }} {{ order.code }}</title>

    {{ settings.font_css }}

    <style>
        {{ invoice_css | raw }}

        body {
            font-family: '{{ settings.font_family }}', Arial, sans-serif !important;
        }

        /* Override table alignment for proper left/right layout */
        .invoice-info-container td:first-child {
            text-align: left !important;
            vertical-align: top;
            width: 50%;
            padding-right: 20px;
        }
        
        .invoice-info-container td:last-child {
            text-align: right !important;
            vertical-align: top;
            width: 50%;
            padding-left: 20px;
        }

        {{ settings.extra_css }}
    </style>

    {{ settings.header_html }}
</head>
<body {{ body_attributes }}>

<table class="invoice-info-container">
    <tr>
        <td>
            <div class="logo-container">
                {% if company_logo_full_path %}
                    <img src="{{ company_logo_full_path }}" style="width:100%; max-width:150px;">
                {% endif %}
            </div>
        </td>
        <td>
            <p>
                <strong>{{ order.created_at|date(settings.date_format) }}</strong>
            </p>
            <p>
                <strong style="display: inline-block">{{ 'plugins/ecommerce::picking-list-template.pakbon'|trans }}: </strong>
                <span style="display: inline-block">{{ order.code }}</span>
            </p>
        </td>
    </tr>
</table>

<table class="invoice-info-container">
    <tr>
        <td>
            {% if company_name %}
                <p><strong>{{ company_name }}</strong></p>
            {% endif %}
            {% if company_address %}
                <div>{{ company_address|nl2br }}</div>
            {% endif %}
            {% if company_city or company_state or company_zipcode %}
                <p>
                    {% if company_zipcode %}{{ company_zipcode }}{% endif %}
                    {% if company_city %}{% if company_zipcode %} {% endif %}{{ company_city }}{% endif %}
                    {% if company_state %}{% if company_city or company_zipcode %}, {% endif %}{{ company_state }}{% endif %}
                </p>
            {% endif %}
            {% if company_country %}
                <p>{{ company_country }}</p>
            {% endif %}
            {% if company_phone %}
                <p>Tel: {{ company_phone }}</p>
            {% endif %}
            {% if company_email %}
                <p>Email: {{ company_email }}</p>
            {% endif %}
        </td>
        <td>
            {% if order.address.name %}
                <p><strong>{{ order.address.name }}</strong></p>
            {% endif %}
            {% if order.address.email %}
                <p>Email: {{ order.address.email }}</p>
            {% endif %}
            {% if order.address.phone %}
                <p>Tel: {{ order.address.phone }}</p>
            {% endif %}
            {% if order.address.address %}
                <div>{{ order.address.address|nl2br }}</div>
            {% endif %}
            {% if order.address.city or order.address.state or order.address.zip_code %}
                <p>
                    {% if order.address.zip_code %}{{ order.address.zip_code }}{% endif %}
                    {% if order.address.city %}{% if order.address.zip_code %} {% endif %}{{ order.address.city }}{% endif %}
                    {% if order.address.state %}{% if order.address.city or order.address.zip_code %}, {% endif %}{{ order.address.state }}{% endif %}
                </p>
            {% endif %}
            {% if order.address.country %}
                <p>{{ order.address.country }}</p>
            {% endif %}
        </td>
    </tr>
</table>

<table class="line-items-container">
    <thead>
    <tr>
        <th class="heading-description">{{ 'plugins/ecommerce::products.form.product'|trans }}</th>
        <th class="heading-options">{{ 'plugins/ecommerce::products.form.options'|trans }}</th>
        <th class="heading-quantity">{{ 'plugins/ecommerce::products.form.quantity'|trans }}</th>
    </tr>
    </thead>
    <tbody>
    {% for item in order.products %}
        <tr class="product-row">
            <td class="product-cell">
                <div class="product-name">{{ item.product_name }}</div>
            </td>
            <td class="options-cell">
                {% if item.product_options %}
                    {% for option_key, option_value in item.product_options %}
                        {% if option_value %}
                            <div class="option-item">
                                <strong>{{ option_key }}:</strong> {{ option_value }}
                            </div>
                        {% endif %}
                    {% endfor %}
                {% else %}
                    <span class="text-muted">-</span>
                {% endif %}
            </td>
            <td class="qty-cell">{{ item.qty }}</td>
        </tr>
    {% endfor %}
    </tbody>
</table>

{{ settings.footer_html }}
</body>
</html>
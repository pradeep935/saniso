<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Order List - {{ $order->code }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 8px;
            vertical-align: top;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f4f4f4;
            text-align: left;
        }

        td.quantity {
            text-align: right;
        }

        header table {
            border: none;
            margin-top: 0;
        }

        header td {
            border: none;
        }

        .logo img {
            height: 100px;
            width: auto;
        }

        .order-info {
            text-align: right;
        }

        .order-info h2 {
            margin: 0;
            font-size: 14px;
            color: #555;
        }

        .order-info p {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .product-name {
            font-weight: bold;
        }

        .product-sku {
            font-size: 12px;
            color: #555;
        }

        .product-options div {
            font-size: 12px;
        }
    </style>
</head>
<body>

    <header>
        <table style="width: 100%; border: none;">
            <tr>
                <td class="logo">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Company Logo">
                    @endif
                </td>
                <td class="order-info">
                    <h2>{{ $order->created_at->format('F d, Y') }}</h2>
                    <p>Order ID: {{ $order->code }}</p>
                </td>
            </tr>
        </table>
    </header>

    <table>
        <thead>
            <tr>
                <th>PRODUCT</th>
                <th>OPTIONS</th>
                <th style="text-align:right;">QUANTITY</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->products as $item)
                <tr>
                    <td>
                        <div class="product-name">{{ $item->product_name }}</div>
                        <div class="product-sku">EAN code: {{ $item->sku ?? '-' }}</div>
                    </td>
                    <td class="product-options">
                        @if(!empty($item->product_options) && is_array($item->product_options))
                            @foreach($item->product_options as $key => $value)
                                <div><strong>{{ $key }}:</strong> {{ $value }}</div>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td class="quantity">{{ $item->qty }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

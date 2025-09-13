<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order['order_id'] }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .invoice-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .invoice-title {
            color: #007bff;
            font-size: 28px;
            font-weight: bold;
            margin: 0;
        }
        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .invoice-info-left, .invoice-info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .invoice-info-right {
            text-align: right;
        }
        .section-title {
            color: #007bff;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .info-row {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .products-table th,
        .products-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .products-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        .products-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-section {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .total-row.final {
            font-weight: bold;
            font-size: 18px;
            color: #007bff;
            border-top: 2px solid #007bff;
            border-bottom: 2px solid #007bff;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <h1 class="invoice-title">INVOICE</h1>
    </div>

    <div class="invoice-info">
        <div class="invoice-info-left">
            <div class="section-title">Bill To:</div>
            <div class="info-row">
                <span class="info-label">Name:</span> {{ $customer['name'] }}
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span> {{ $customer['email'] }}
            </div>
            @if(isset($customer['phone']) && $customer['phone'])
            <div class="info-row">
                <span class="info-label">Phone:</span> {{ $customer['phone'] }}
            </div>
            @endif
            @if(isset($customer['address']) && $customer['address'])
            <div class="info-row">
                <span class="info-label">Address:</span> {{ $customer['address'] }}
            </div>
            @endif
        </div>
        <div class="invoice-info-right">
            <div class="section-title">Invoice Details:</div>
            <div class="info-row">
                <span class="info-label">Invoice Number:</span> {{ $invoice_number }}
            </div>
            <div class="info-row">
                <span class="info-label">Order ID:</span> {{ $order['order_id'] }}
            </div>
            <div class="info-row">
                <span class="info-label">Order Date:</span> {{ $order['date'] }}
            </div>
            <div class="info-row">
                <span class="info-label">Invoice Date:</span> {{ $invoice_date }}
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span> 
                <span class="status-badge status-{{ strtolower($order['status']) }}">
                    {{ $order['status'] }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Method:</span> {{ $order['payment_method'] }}
            </div>
        </div>
    </div>

    <div class="section-title">Products Ordered:</div>
    <table class="products-table">
        <thead>
            <tr>
                <th>Product</th>
                <th class="text-center">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Tax</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>
                    <strong>{{ $product['name'] }}</strong>
                    @if(isset($product['description']) && $product['description'])
                    <br><small style="color: #666;">{{ $product['description'] }}</small>
                    @endif
                </td>
                <td class="text-center">{{ $product['quantity'] }}</td>
                <td class="text-right">${{ number_format($product['unit_price'], 2) }}</td>
                <td class="text-right">${{ number_format($product['subtotal'], 2) }}</td>
                <td class="text-right">${{ number_format($product['total_tax'], 2) }}</td>
                <td class="text-right">${{ number_format($product['total_payable'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span>Total Tax:</span>
            <span>${{ number_format($order['total_tax'], 2) }}</span>
        </div>
        <div class="total-row final">
            <span>Total Amount:</span>
            <span>${{ number_format($order['total_payable'], 2) }}</span>
        </div>
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>This is a system-generated invoice.</p>
    </div>
</body>
</html> 
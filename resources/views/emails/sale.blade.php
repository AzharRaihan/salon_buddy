<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $subject }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            font-size: 16px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header {
            background: linear-gradient(to right, #7367f0, #7367f0);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .content {
            background: #ffffff;
            padding: 30px 25px;
            border: 1px solid #e0e0e0;
            border-top: none;
            border-radius: 0 0 10px 10px;
        }

        .content h2 {
            font-size: 22px;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .content p {
            margin-bottom: 16px;
            font-size: 16px;
        }

        .booking-details {
            background-color: #fdf2f7;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            border: 1px solid #f8d7da;
        }

        .booking-details h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .highlight {
            color: #7367f0;
            font-weight: 600;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            font-size: 13px;
            color: #777;
            border-top: 1px solid #eaeaea;
        }

        .footer p {
            margin-bottom: 10px;
        }

        .rating-section a:hover {
            background-color: #5a5fcf !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛍️ {{ $companyName }} Sale Confirmation 🛍️</h1>
        </div>

        <div class="content">
            <h2>Dear {{ $customerName }},</h2>

            <p>Thank you for shopping with <strong>{{ $companyName }}</strong>! We’re thrilled to confirm your recent purchase.</p>

            <div class="sale-details">
                <h3>Your Order Details:</h3>
                <p>🏠 <strong>Branch:</strong> <span class="highlight">{{ $branchName }}</span></p>
                <p>📅 <strong>Sale Date:</strong> <span class="highlight">{{ $date }}</span></p>
                <p>🔖 <strong>Sale Reference:</strong> <span class="highlight">{{ $referenceNo }}</span></p>
                <p>💳 <strong>Payment Status:</strong> <span class="highlight">{{ $status }}</span></p>
                <p>💰 <strong>Total Amount:</strong> <span class="highlight">{{ $totalPayable }}</span></p>
                <p>💰 <strong>Total Paid:</strong> <span class="highlight">{{ $totalPaid }}</span></p>
                <p>💰 <strong>Total Due:</strong> <span class="highlight">{{ $totalDue }}</span></p>
            </div>

            <!-- Rating Section -->
            <div class="rating-section" style="text-align: center; margin: 30px 0;">
                <h3 style="color: #333; margin-bottom: 20px;">Rate Your Services</h3>
                <p style="color: #666; margin-bottom: 25px;">We value your feedback! Please take a moment to rate the services you received.</p>
                <!-- send customer id  with encrypted -->
                <a href="{{ url('/ratting') . '?reference=' . urlencode(Crypt::encrypt($referenceNo)) . '&customerId=' . urlencode(Crypt::encrypt($customerId)) }}"
                style="background-color: #7367f0; color: white; padding: 15px 35px; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 500; display: inline-block; transition: background-color 0.3s;">
                    Give Rating
                </a>
            </div>

            <div class="footer">
                <p>🎉 Thank you for your purchase! 🎉</p>
            </div>
        </div>
    </div>

</body>
</html>

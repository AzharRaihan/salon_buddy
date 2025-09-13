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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 Password Reset 🔒</h1>
        </div>

        <div class="content">
            <h2>Dear {{ $customerName }},</h2>
            <p>Your password has been reset. Please use the following temporary password to login:</p>
            <p><strong>Password: {{ $tempPassword }}</strong></p>
            <p>Please change your password after logging in.</p>
            <p>Login URL: <a href="{{ url('/frontend/login') }}">Login</a></p>
            <div class="footer">
                <p>🎉 Thank you for using <strong>{{ $companyName }}</strong>! 🎉</p>
            </div>
        </div>
    </div>
</body>
</html>

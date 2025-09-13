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

        .package-details {
            background-color: #fff7e6;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            border: 1px solid #ffe0b2;
        }

        .package-details h3 {
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
        .table {
            width: 100%;
            border: 1px solid #e0e0e0;
            padding: 10px;
            border-radius: 8px;
        }
        .text-center {
            text-align: center;
        }
        .w-10 {
            width: 10%;
        }
        .w-15 {
            width: 15%;
        }
        .w-35 {
            width: 35%;
        }
        .w-30 {
            width: 30%;
        }
        .w-20 {
            width: 20%;
        }
        .text-left {
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💆 {{ $companyName }} Package Update 💆</h1>
        </div>

        <div class="content">
            <h2>Dear {{ $package_info['data']['package_summary']['customer']['name'] }},</h2>

            <p>We hope you enjoyed your recent service with us! Here’s an update on your package usage:</p>

            <div class="package-details">
                <h3>Package Service & Items:</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-10 text-left">SN</th>
                            <th class="w-30 text-left">Service</th>
                            <th class="w-20 text-center">Package Qty</th>
                            <th class="w-20 text-center">Taken Qty</th>
                            <th class="w-20 text-center">Remaining Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($package_info['data']['included_items'] as $key => $item)
                        <tr>
                            <td class="text-left">{{ $key + 1 ?? '' }}</td>
                            <td class="text-left">{{ $item['service_name'] }}</td>
                            <td class="text-center">{{ $item['package_qty'] }}</td>
                            <td class="text-center">{{ $item['taken'] }}</td>
                            <td class="text-center">{{ $item['remaining'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <h3>Package Usages Summary:</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-10 text-left">SN</th>
                            <th class="w-30 text-left">Service</th>
                            <th class="w-20 text-center">Usages Date</th>
                            <th class="w-20 text-center">Usages Time</th>
                            <th class="w-20 text-center">Usages Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($package_info['data']['usages_summary'] as $key => $item)
                        <tr>
                            <td class="text-left">{{ $key + 1 ?? '' }}</td>
                            <td class="text-left">{{ $item['service_item'] }}</td>
                            <td class="text-center">
                                {{ \Carbon\Carbon::parse($item['usages_date'])->format('Y/m/d') }}
                            </td>
                            <td class="text-center">
                                {{ \Carbon\Carbon::parse($item['usages_time'])->format('H:i') }}
                            </td>
                            <td class="text-center">{{ $item['taken_qty'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p>Thank you for continuing your beauty journey with us. We look forward to seeing you again soon! 💖</p>

            <div class="footer">
                <p>✨ {{ $companyName }} – Where beauty meets relaxation ✨</p>
            </div>
        </div>
    </div>
</body>
</html>

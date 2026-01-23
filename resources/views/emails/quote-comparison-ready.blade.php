<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 {
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .best-rate {
            background: #e8f5e9;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <h2>Your Insurance Quote is Ready!</h2>

    <p>Dear {{ $quote->client_name ?? $quote->client->name ?? 'Valued Customer' }},</p>

    <p>We've prepared a comparison of insurance quotes for your <strong>{{ $quote->insuranceType->name }}</strong> coverage request.</p>

    <h3>Quote Number: {{ $quote->quote_number }}</h3>

    @if(!empty($quote->comparison_data['insurers']))
    <table>
        <thead>
            <tr>
                <th>Insurer</th>
                <th>Premium (MZN)</th>
                <th>Turnaround</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->comparison_data['insurers'] as $index => $insurer)
            <tr class="{{ $index === 0 ? 'best-rate' : '' }}">
                <td>
                    {{ $insurer['insurer_name'] }}
                    @if($index === 0)
                        <span style="color: #4caf50;">✓ BEST RATE</span>
                    @endif
                </td>
                <td>{{ number_format($insurer['calculated_cost'], 2) }}</td>
                <td>{{ $insurer['turnaround_days'] }} days</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Best Rate: {{ number_format($quote->comparison_data['insurers'][0]['calculated_cost'], 2) }} MZN</strong></p>
    @endif

    <p>To proceed with your preferred insurer, please contact us or reply to this email.</p>

    <p>Thank you for choosing Skyydo!</p>

    <div class="footer">
        <p>This is an automated message from Skyydo Insurance Management Platform.</p>
        <p>If you have any questions, please contact our support team.</p>
    </div>
</body>
</html>

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
        .quote-number {
            background: #f0f0f0;
            padding: 10px;
            border-left: 4px solid #3498db;
            margin: 20px 0;
        }
        .info-box {
            background: #e8f4f8;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
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
    <h2>Quote Request Received</h2>

    <p>Dear {{ $quote->client_name ?? $quote->client->name ?? 'Valued Customer' }},</p>

    <p>Thank you for your <strong>{{ $quote->insuranceType->name }}</strong> insurance quote request.</p>

    <div class="quote-number">
        <strong>Quote Number: {{ $quote->quote_number }}</strong>
    </div>

    <div class="info-box">
        <p><strong>What happens next?</strong></p>
        <p>Due to the specific requirements of your request, one of our insurance specialists will review it and contact you within 24 hours with personalized quotes from our partner insurers.</p>
    </div>

    <p>We appreciate your patience and look forward to helping you find the best coverage for your needs.</p>

    <p>Best regards,<br>
    <strong>Skyydo Insurance Team</strong></p>

    <div class="footer">
        <p>This is an automated message from Skyydo Insurance Management Platform.</p>
        <p>If you have any urgent questions, please contact our support team.</p>
    </div>
</body>
</html>

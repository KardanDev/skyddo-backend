<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .quote-details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #4F46E5;
        }
        .detail-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            width: 150px;
            color: #6b7280;
        }
        .detail-value {
            flex: 1;
            color: #111827;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4F46E5;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>New Quote Request</h1>
    </div>

    <div class="content">
        <p>Dear {{ $quote->insurer->contact_person ?? $quote->insurer->name }},</p>

        <p>We have a new insurance quote request that requires your attention.</p>

        <div class="quote-details">
            <h2 style="margin-top: 0; color: #4F46E5;">Quote Details</h2>

            <div class="detail-row">
                <span class="detail-label">Quote Number:</span>
                <span class="detail-value"><strong>{{ $quote->quote_number }}</strong></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Insurance Type:</span>
                <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $quote->insurance_type)) }}</span>
            </div>

            @if($quote->sum_insured)
            <div class="detail-row">
                <span class="detail-label">Sum Insured:</span>
                <span class="detail-value">${{ number_format($quote->sum_insured, 2) }}</span>
            </div>
            @endif

            @if($quote->description)
            <div class="detail-row">
                <span class="detail-label">Description:</span>
                <span class="detail-value">{{ $quote->description }}</span>
            </div>
            @endif

            @if($quote->valid_until)
            <div class="detail-row">
                <span class="detail-label">Valid Until:</span>
                <span class="detail-value">{{ $quote->valid_until->format('M d, Y') }}</span>
            </div>
            @endif
        </div>

        <div class="quote-details">
            <h2 style="margin-top: 0; color: #4F46E5;">Client Information</h2>

            <div class="detail-row">
                <span class="detail-label">Client Name:</span>
                <span class="detail-value">{{ $quote->client->name }}</span>
            </div>

            @if($quote->client->email)
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value">{{ $quote->client->email }}</span>
            </div>
            @endif

            @if($quote->client->phone)
            <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span class="detail-value">{{ $quote->client->phone }}</span>
            </div>
            @endif

            @if($quote->client->company_name)
            <div class="detail-row">
                <span class="detail-label">Company:</span>
                <span class="detail-value">{{ $quote->client->company_name }}</span>
            </div>
            @endif
        </div>

        <p style="margin-top: 30px;">
            Please review this quote request and provide your response at your earliest convenience.
        </p>

        <p>If you have any questions or need additional information, please don't hesitate to contact us.</p>

        <p style="margin-top: 30px;">
            Best regards,<br>
            <strong>{{ config('app.name') }}</strong>
        </p>
    </div>

    <div class="footer">
        <p>This is an automated message from {{ config('app.name') }}. Please do not reply directly to this email.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>

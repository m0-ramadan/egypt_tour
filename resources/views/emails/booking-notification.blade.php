<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>New booking</title>
</head>
<body style="margin:0;background:#f4f7fb;font-family:Arial,sans-serif;color:#172033">
    <div style="max-width:720px;margin:0 auto;padding:28px 14px">
        <div style="background:#0b2554;color:#fff;padding:26px;border-radius:14px 14px 0 0">
            <div style="font-size:13px;color:#ffbc75;text-transform:uppercase;letter-spacing:1px">Egypt Tour Pro</div>
            <h1 style="margin:8px 0 4px;font-size:25px">{{ $isTest ? 'Test booking notification' : 'New booking received' }}</h1>
            <div style="color:#dce7f7">Reference: <strong>{{ $details['booking_number'] }}</strong></div>
        </div>

        <div style="background:#fff;padding:26px;border-radius:0 0 14px 14px">
            @foreach ($details['sections'] as $section => $rows)
                <h2 style="font-size:17px;color:#0b2554;border-bottom:2px solid #f36b0a;padding-bottom:8px;margin:{{ $loop->first ? '0' : '26px' }} 0 12px">{{ $section }}</h2>
                <table role="presentation" style="width:100%;border-collapse:collapse">
                    @foreach ($rows as $label => $value)
                        <tr>
                            <td style="width:34%;padding:8px 6px;color:#64748b;vertical-align:top;border-bottom:1px solid #edf1f5">{{ $label }}</td>
                            <td style="padding:8px 6px;font-weight:600;white-space:pre-line;border-bottom:1px solid #edf1f5">{{ $value === '' || $value === null ? 'N/A' : $value }}</td>
                        </tr>
                    @endforeach
                </table>
            @endforeach

            <p style="margin:26px 0 0;color:#64748b;font-size:12px">Submitted at {{ $details['submitted_at'] }} (Africa/Cairo).</p>
        </div>
    </div>
</body>
</html>

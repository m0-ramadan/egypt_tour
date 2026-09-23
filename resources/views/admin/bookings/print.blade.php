@include('admin.i18n.locale')
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>Print Booking</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            direction: rtl;
            margin: 30px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .box {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
        }

        .row {
            margin-bottom: 10px;
        }

        .label {
            font-weight: bold;
            display: inline-block;
            min-width: 160px;
        }

        .title {
            font-size: 26px;
            margin-bottom: 10px;
        }

        .print-btn {
            margin-bottom: 20px;
        }

        @media print {
            .print-btn {
                display: none;
            }
        }
    </style>
</head>

<body>

    <button onclick="window.print()" class="print-btn">Print</button>

    <div class="header">
        <div class="title">Details Booking</div>
        <div>Booking Reference: {{ $booking->booking_reference ?? '-' }}</div>
    </div>

    <div class="box">
        <div class="row"><span class="label">Client: Name</span>
            {{ $booking->client->name ?? ($booking->client_name ?? '-') }}</div>
        <div class="row"><span class="label">Email Electronic:</span> {{ $booking->email ?? '-' }}</div>
        <div class="row"><span class="label">Phone:</span> {{ $booking->phone ?? '-' }}</div>
    </div>

    <div class="box">
        <div class="row"><span class="label">Package:</span> {{ $booking->package->name ?? '-' }}</div>
        <div class="row"><span class="label">Status:</span> {{ $booking->status ?? '-' }}</div>
        <div class="row"><span class="label">Number of Guests:</span> {{ $booking->travellers_count ?? '-' }} ({{ $booking->adults ?? 0 }} Adults · {{ $booking->children ?? 0 }} Children · {{ $booking->infants ?? 0 }} Infants)</div>
        <div class="row"><span class="label">Travel Date:</span>
            {{ optional($booking->travel_date)->translatedFormat('d M Y') ?? '-' }}</div>
    </div>

    <div class="box">
        <div class="row"><span class="label">Total Price:</span>
            {{ number_format($booking->total_amount ?? 0, 2) }} {{ $booking->currency_code ?? '' }}</div>
        <div class="row"><span class="label">Created At:</span>
            {{ optional($booking->created_at)->translatedFormat('d M Y - h:i A') ?? '-' }}</div>
    </div>

    @if($booking->items->isNotEmpty())
        <div class="box">
            @foreach($booking->items as $item)
                <div class="row"><span class="label">Accommodation / Cabin:</span> {{ $item->option_label }}</div>
                <div class="row"><span class="label">Occupancy Type:</span> {{ $item->occupancy_type ?: '-' }}</div>
                <div class="row"><span class="label">Number of Rooms / Cabins:</span> {{ $item->room_count }}</div>
            @endforeach
        </div>
    @endif

    @if($booking->travelers->isNotEmpty())
        <div class="box">
            <div class="row"><span class="label">Passengers:</span></div>
            @foreach($booking->travelers as $traveler)
                @php
                    $typeLabel = match($traveler->traveler_type) {
                        'infant' => 'Infant',
                        'child' => 'Child',
                        default => 'Adult',
                    };
                @endphp
                <div class="row">{{ $loop->iteration }}. {{ $traveler->title }} {{ $traveler->first_name }} {{ $traveler->last_name }} ({{ $typeLabel }})</div>
            @endforeach
        </div>
    @endif

    <div class="box">
        <div class="row"><span class="label">Notes:</span></div>
        <div>{{ $booking->notes ?: 'No Notes' }}</div>
    </div>

</body>

</html>

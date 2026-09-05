@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('سجل التواصل'))

@section('css')

    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-color: #20c997;
            --danger-color: #fd7e14;
            --info-color: #0c63e4;
            --warning-color: #ffc107;
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
        }

        body {
            font-family: "Cairo", sans-serif !important;
            background: var(--dark-bg);
            color: #fff;
        }

        .main-card {
            background: var(--dark-card);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
            padding: 0;
            border: 1px solid rgba(255, 255, 255, .1);
            overflow: hidden;
        }

        .main-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 25px 30px;
        }

        .stats-card {
            background: var(--dark-card);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .3);
            border-top: 4px solid var(--primary-color);
            transition: transform .3s ease;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stats-number {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }

        .stats-label {
            color: rgba(255, 255, 255, .7);
            font-size: 14px;
        }

        .filter-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .item-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, .1);
            transition: all .3s ease;
        }

        .item-card:hover {
            transform: translateX(-5px);
            background: rgba(105, 108, 255, .1);
            border-color: var(--primary-color);
        }

        .badge-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .type-client {
            background: rgba(32, 201, 151, .2);
            color: #20c997;
            border: 1px solid rgba(32, 201, 151, .3);
        }

        .type-booking {
            background: rgba(12, 99, 228, .2);
            color: #0c63e4;
            border: 1px solid rgba(12, 99, 228, .3);
        }

        .type-inquiry {
            background: rgba(255, 193, 7, .2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, .3);
        }

        .type-other {
            background: rgba(253, 126, 20, .2);
            color: #fd7e14;
            border: 1px solid rgba(253, 126, 20, .3);
        }
    </style>
@endsection

@section('content')
    @php
        $totalCommunications = $communications->total() ?? $communications->count();
        $clientCount = \App\Models\Communication::where('related_type', 'client')->count();
        $bookingCount = \App\Models\Communication::where('related_type', 'booking')->count();
        $inquiryCount = \App\Models\Communication::where('related_type', 'inquiry')->count();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item active">سجل التواصل</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stats-number">{{ number_format($totalCommunications) }}</div>
                    <div class="stats-label">إجمالي السجلات</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(32,201,151,.2); color:#20c997; border:1px solid rgba(32,201,151,.3);">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="stats-number">{{ number_format($clientCount) }}</div>
                    <div class="stats-label">تواصل العملاء</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(12,99,228,.2); color:#0c63e4; border:1px solid rgba(12,99,228,.3);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stats-number">{{ number_format($bookingCount) }}</div>
                    <div class="stats-label">تواصل الحجوزات</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(255,193,7,.2); color:#ffc107; border:1px solid rgba(255,193,7,.3);">
                        <i class="fas fa-circle-question"></i>
                    </div>
                    <div class="stats-number">{{ number_format($inquiryCount) }}</div>
                    <div class="stats-label">تواصل الاستفسارات</div>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="{{ route('admin.communications.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                            placeholder="ابحث في الموضوع أو الرسالة">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">النوع</label>
                        <select name="related_type" class="form-select">
                            <option value="">الكل</option>
                            <option value="client" {{ request('related_type') == 'client' ? 'selected' : '' }}>client
                            </option>
                            <option value="booking" {{ request('related_type') == 'booking' ? 'selected' : '' }}>booking
                            </option>
                            <option value="inquiry" {{ request('related_type') == 'inquiry' ? 'selected' : '' }}>inquiry
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">القناة</label>
                        <input type="text" name="channel" class="form-control" value="{{ request('channel') }}"
                            placeholder="email / whatsapp / phone">
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary w-100" type="submit">فلترة</button>
                        <a href="{{ route('admin.communications.index') }}" class="btn btn-secondary w-100">إعادة</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="main-card">
            <div class="main-header">
                <h5 class="mb-0">سجل التواصل</h5>
                <small class="opacity-75">كل الرسائل والمكالمات والتفاعلات المسجلة</small>
            </div>

            <div class="p-4">
                @forelse($communications as $communication)
                    @php
                        $typeClass = match ($communication->related_type) {
                            'client' => 'type-client',
                            'booking' => 'type-booking',
                            'inquiry' => 'type-inquiry',
                            default => 'type-other',
                        };
                    @endphp

                    <div class="item-card">
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                            <div>
                                <h6 class="mb-1">{{ $communication->subject ?? 'بدون عنوان' }}</h6>
                                <small class="text-light opacity-75">{{ $communication->channel ?? '-' }}</small>
                            </div>

                            <span class="badge-status {{ $typeClass }}">
                                {{ $communication->related_type ?? 'other' }}
                            </span>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Related ID:</strong> {{ $communication->related_id ?? '-' }}
                            </div>
                            <div class="col-md-3"><strong>Sender:</strong> {{ $communication->sender_name ?? '-' }}</div>
                            <div class="col-md-3"><strong>Receiver:</strong> {{ $communication->receiver_name ?? '-' }}
                            </div>
                            <div class="col-md-3"><strong>التاريخ:</strong>
                                {{ optional($communication->created_at)->translatedFormat('d M Y - h:i A') ?? '-' }}</div>
                        </div>

                        <div class="mb-3">
                            <strong>الرسالة:</strong>
                            {{ \Illuminate\Support\Str::limit($communication->message ?? '-', 220) }}
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.communications.show', $communication) }}"
                                class="btn btn-info btn-sm">عرض</a>

                            @if (($communication->related_type ?? null) === 'client' && Route::has('admin.communications.client'))
                                <a href="{{ route('admin.communications.client', $communication) }}"
                                    class="btn btn-success btn-sm">سجل العميل</a>
                            @endif

                            @if (($communication->related_type ?? null) === 'booking' && Route::has('admin.communications.booking'))
                                <a href="{{ route('admin.communications.booking', $communication) }}"
                                    class="btn btn-primary btn-sm">سجل الحجز</a>
                            @endif

                            @if (($communication->related_type ?? null) === 'inquiry' && Route::has('admin.communications.inquiry'))
                                <a href="{{ route('admin.communications.inquiry', $communication) }}"
                                    class="btn btn-warning btn-sm">سجل الاستفسار</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">لا توجد سجلات تواصل حالياً</div>
                @endforelse

                @if (method_exists($communications, 'links'))
                    <div class="mt-4">{{ $communications->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection

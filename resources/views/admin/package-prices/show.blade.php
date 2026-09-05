@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('عرض سعر الباقة'))

@section('css')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
        }

        body {
            font-family: "Cairo", sans-serif !important;
            background: var(--dark-bg);
            color: #fff;
        }

        .profile-card {
            background: var(--dark-card);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .profile-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 30px;
        }

        .profile-body {
            padding: 30px;
        }

        .info-box {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
        }

        .info-label {
            color: rgba(255, 255, 255, .65);
            font-size: 13px;
            margin-bottom: 5px;
        }

        .info-value {
            color: #fff;
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.package-prices.index') }}">أسعار الباقات</a></li>
                <li class="breadcrumb-item active">عرض السعر</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">{{ $packagePrice->package->name ?? '-' }}</h4>
                    <small class="opacity-75">{{ $packagePrice->label ?? '-' }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.package-prices.edit', $packagePrice) }}" class="btn btn-light">تعديل</a>
                    <a href="{{ route('admin.package-prices.index') }}" class="btn btn-outline-light">رجوع</a>
                </div>
            </div>

            <div class="profile-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الباقة</div>
                            <div class="info-value">{{ $packagePrice->package->name ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">العملة</div>
                            <div class="info-value">{{ $packagePrice->currency->code ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">المبلغ</div>
                            <div class="info-value">{{ number_format($packagePrice->amount ?? 0, 2) }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Season Name</div>
                            <div class="info-value">{{ $packagePrice->season_name ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Price Type</div>
                            <div class="info-value">{{ $packagePrice->price_type ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Room Type</div>
                            <div class="info-value">{{ $packagePrice->room_type ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">Pax Min</div>
                            <div class="info-value">{{ $packagePrice->pax_min ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">Pax Max</div>
                            <div class="info-value">{{ $packagePrice->pax_max ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">Group Min</div>
                            <div class="info-value">{{ $packagePrice->group_size_min ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">Group Max</div>
                            <div class="info-value">{{ $packagePrice->group_size_max ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Valid From</div>
                            <div class="info-value">
                                {{ optional($packagePrice->valid_from)->translatedFormat('d M Y') ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Valid To</div>
                            <div class="info-value">
                                {{ optional($packagePrice->valid_to)->translatedFormat('d M Y') ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">Notes</div>
                            <div class="info-value">{{ $packagePrice->notes ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

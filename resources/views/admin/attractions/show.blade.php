@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('عرض المعلم'))

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
            border: 1px solid rgba(255, 255, 255, .1);
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
        }

        .profile-header {
            background: var(--primary-gradient);
            padding: 30px;
            color: #fff;
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
            height: 100%;
        }

        .info-label {
            color: rgba(255, 255, 255, .65);
            font-size: 13px;
            margin-bottom: 5px;
        }

        .info-value {
            color: #fff;
            font-weight: 600;
            line-height: 1.8;
        }

        .attraction-image {
            width: 100%;
            max-height: 340px;
            object-fit: cover;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, .1);
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">{{ adminTrans($attraction->name) ?: 'بدون اسم' }}</h4>
                    <small class="opacity-75">{{ $attraction->slug ?: '-' }}</small>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.attractions.edit', $attraction) }}" class="btn btn-warning">تعديل</a>
                    <a href="{{ route('admin.attractions.index') }}" class="btn btn-light">رجوع</a>
                </div>
            </div>

            <div class="profile-body">
                @if ($attraction->image)
                    <div class="mb-4">
                        <img src="{{ asset('storage/' . $attraction->image) }}" class="attraction-image"
                            alt="attraction image">
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">الاسم</div>
                            <div class="info-value">{{ adminTrans($attraction->name) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">المدينة</div>
                            <div class="info-value">{{ adminTrans(optional($attraction->city)->name) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Slug</div>
                            <div class="info-value">{{ $attraction->slug ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الحالة</div>
                            <div class="info-value">{{ $attraction->is_active ? 'مفعل' : 'غير مفعل' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">مميز</div>
                            <div class="info-value">{{ $attraction->is_featured ? 'نعم' : 'لا' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الترتيب</div>
                            <div class="info-value">{{ $attraction->sort_order ?? 0 }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">خط العرض</div>
                            <div class="info-value">{{ $attraction->latitude ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">خط الطول</div>
                            <div class="info-value">{{ $attraction->longitude ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">ساعات العمل</div>
                            <div class="info-value">{{ $attraction->opening_hours ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">رابط الخريطة</div>
                            <div class="info-value">
                                @if ($attraction->map_url)
                                    <a href="{{ $attraction->map_url }}" target="_blank" class="text-info">فتح الرابط</a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">الوصف المختصر</div>
                            <div class="info-value">{!! adminTrans($attraction->short_description) ?: '-' !!}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">الوصف</div>
                            <div class="info-value">{!! adminTrans($attraction->description) ?: '-' !!}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">SEO Title</div>
                            <div class="info-value">{{ adminTrans($attraction->seo_title) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">SEO Description</div>
                            <div class="info-value">{{ adminTrans($attraction->seo_description) ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

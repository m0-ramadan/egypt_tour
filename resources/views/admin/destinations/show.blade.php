@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('عرض الوجهة'))

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
            word-break: break-word;
        }

        .destination-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, .1);
            background: rgba(255, 255, 255, .05);
        }

        .mini-image {
            width: 55px;
            height: 55px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid rgba(255, 255, 255, .15);
            background: rgba(255, 255, 255, .08);
        }

        .image-box {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
        }

        .badge-soft {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .badge-success-soft {
            background: rgba(25, 135, 84, .15);
            color: #75d39b;
            border: 1px solid rgba(25, 135, 84, .25);
        }

        .badge-danger-soft {
            background: rgba(220, 53, 69, .15);
            color: #ff8b98;
            border: 1px solid rgba(220, 53, 69, .25);
        }

        .badge-warning-soft {
            background: rgba(255, 193, 7, .15);
            color: #ffd86b;
            border: 1px solid rgba(255, 193, 7, .25);
        }

        pre.schema-box {
            background: rgba(0, 0, 0, .2);
            color: #fff;
            border-radius: 12px;
            padding: 15px;
            white-space: pre-wrap;
            word-break: break-word;
            margin: 0;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.destinations.index') }}">الوجهات</a></li>
                <li class="breadcrumb-item active">عرض الوجهة</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    @if ($destination->featured_image)
                        <img src="{{ asset($destination->featured_image) }}" alt="{{ adminTrans($destination->name) }}"
                            class="mini-image">
                    @elseif($destination->hero_image)
                        <img src="{{ asset($destination->hero_image) }}" alt="{{ adminTrans($destination->name) }}"
                            class="mini-image">
                    @endif

                    <div>
                        <h4 class="mb-1">{{ adminTrans($destination->name) ?: 'بدون اسم' }}</h4>
                        <small class="opacity-75">{{ $destination->slug ?? '-' }}</small>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.destinations.edit', $destination) }}" class="btn btn-light">تعديل</a>
                    <a href="{{ route('admin.destinations.index') }}" class="btn btn-outline-light">رجوع</a>
                </div>
            </div>

            <div class="profile-body">
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="image-box">
                            <div class="info-label mb-2">Hero Image</div>
                            @if ($destination->hero_image)
                                <img src="{{ asset($destination->hero_image) }}"
                                    alt="{{ adminTrans($destination->name) }}" class="destination-image">
                            @else
                                <div class="info-value">لا توجد صورة رئيسية</div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="image-box">
                            <div class="info-label mb-2">Featured Image</div>
                            @if ($destination->featured_image)
                                <img src="{{ asset($destination->featured_image) }}"
                                    alt="{{ adminTrans($destination->name) }}" class="destination-image">
                            @else
                                <div class="info-value">لا توجد صورة مميزة</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">النوع</div>
                            <div class="info-value">{{ $destination->type ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الدولة</div>
                            <div class="info-value">{{ adminTrans(optional($destination->country)->name) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">المدينة</div>
                            <div class="info-value">{{ adminTrans(optional($destination->city)->name) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الوجهة الأم</div>
                            <div class="info-value">{{ adminTrans(optional($destination->parent)->name) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الحالة</div>
                            <div class="info-value">
                                @if ($destination->is_active)
                                    <span class="badge-soft badge-success-soft">مفعلة</span>
                                @else
                                    <span class="badge-soft badge-danger-soft">غير مفعلة</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">مميزة</div>
                            <div class="info-value">
                                @if ($destination->is_featured)
                                    <span class="badge-soft badge-warning-soft">نعم</span>
                                @else
                                    <span class="badge-soft badge-danger-soft">لا</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الترتيب</div>
                            <div class="info-value">{{ $destination->sort_order ?? 0 }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Latitude</div>
                            <div class="info-value">{{ $destination->latitude ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Longitude</div>
                            <div class="info-value">{{ $destination->longitude ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">SEO Title</div>
                            <div class="info-value">{{ adminTrans($destination->seo_title) ?: 'لا يوجد' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">SEO Description</div>
                            <div class="info-value">{{ adminTrans($destination->seo_description) ?: 'لا يوجد' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">Short Description</div>
                            <div class="info-value">
                                {{ adminTrans($destination->short_description) ?: 'لا يوجد وصف مختصر' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">الوصف</div>
                            <div class="info-value">{{ adminTrans($destination->description) ?: 'لا يوجد وصف' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">Schema JSON</div>
                            @if ($destination->schema_json)
                                <pre class="schema-box">{{ $destination->schema_json }}</pre>
                            @else
                                <div class="info-value">لا يوجد Schema JSON</div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">تاريخ الإنشاء</div>
                            <div class="info-value">
                                {{ optional($destination->created_at)->translatedFormat('d M Y - h:i A') ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">آخر تحديث</div>
                            <div class="info-value">
                                {{ optional($destination->updated_at)->translatedFormat('d M Y - h:i A') ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

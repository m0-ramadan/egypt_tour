@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('عرض التصنيف'))

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
                <li class="breadcrumb-item"><a href="{{ route('admin.package-categories.index') }}">تصنيفات الباقات</a></li>
                <li class="breadcrumb-item active">عرض التصنيف</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">{{ adminTrans($category->name) ?: 'بدون اسم' }}</h4>
                    <small class="opacity-75">{{ $category->slug ?? '-' }}</small>
                </div>
                <div class="d-flex gap-2">

                    <a href="{{ route('admin.package-categories.edit', $category) }}" class="btn btn-light">تعديل</a>
                    <a href="{{ route('admin.package-categories.index') }}" class="btn btn-outline-light">رجوع</a>
                </div>
            </div>

            <div class="profile-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الحالة</div>
                            <div class="info-value">{{ $category->is_active ? 'مفعل' : 'غير مفعل' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">مميز</div>
                            <div class="info-value">{{ $category->is_featured ? 'نعم' : 'لا' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">نوع التصنيف</div>
                            <div class="info-value">
                                {{ \App\Models\PackageCategory::TYPES[$category->category_type] ?? $category->category_type }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">التصنيف الأب</div>
                            <div class="info-value">{{ $category->parent ? adminTrans($category->parent->name) : '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الدولة</div>
                            <div class="info-value">{{ $category->country ? adminTrans($category->country->name) : '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">التصنيفات الفرعية</div>
                            <div class="info-value">{{ $category->children_count }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">مدة الرحلة</div>
                            <div class="info-value">
                                {{ $category->min_days ?? '-' }} — {{ $category->max_days ?? '-' }} يوم
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">السعر يبدأ من</div>
                            <div class="info-value">{{ $category->price_from !== null ? number_format((float) $category->price_from, 2) : '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الترتيب</div>
                            <div class="info-value">{{ $category->sort_order ?? 0 }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">عدد الباقات</div>
                            <div class="info-value">{{ $category->packages_count ?? ($category->packages->count() ?? 0) }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">تاريخ الإنشاء</div>
                            <div class="info-value">
                                {{ optional($category->created_at)->translatedFormat('d M Y - h:i A') ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">الوصف</div>
                            <div class="info-value">{{ adminTrans($category->description) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Meta Title</div>
                            <div class="info-value">{{ adminTrans($category->seo_title) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Meta Description</div>
                            <div class="info-value">{{ adminTrans($category->seo_description) ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

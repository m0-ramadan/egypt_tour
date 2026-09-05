@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('عرض الرحلة'))

@section('css')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
            --dark-box: rgba(255, 255, 255, .05);
            --dark-border: rgba(255, 255, 255, .1);
        }

        body {
            font-family: "Cairo", sans-serif !important;
            background: var(--dark-bg);
            color: #fff;
        }

        .profile-card {
            background: var(--dark-card);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
            border: 1px solid var(--dark-border);
        }

        .profile-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 30px;
        }

        .profile-body {
            padding: 30px;
        }

        .section-title {
            font-weight: 700;
            font-size: 18px;
            margin: 30px 0 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--dark-border);
        }

        .info-box {
            background: var(--dark-box);
            border: 1px solid var(--dark-border);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
            height: calc(100% - 15px);
        }

        .info-label {
            color: rgba(255, 255, 255, .65);
            font-size: 13px;
            margin-bottom: 6px;
        }

        .info-value {
            color: #fff;
            font-weight: 600;
            white-space: pre-line;
        }

        .badge-soft {
            display: inline-block;
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .12);
            color: #fff;
            padding: 7px 10px;
            border-radius: 999px;
            margin: 3px;
            font-size: 13px;
        }

        .image-preview {
            width: 120px;
            height: 90px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid var(--dark-border);
        }

        .table-dark-custom {
            color: #fff;
            border-color: var(--dark-border);
        }

        .table-dark-custom th,
        .table-dark-custom td {
            color: #fff;
            border-color: var(--dark-border);
            vertical-align: middle;
        }

        .timeline-item {
            background: var(--dark-box);
            border: 1px solid var(--dark-border);
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 14px;
        }
    </style>
@endsection

@section('content')
    @php
        $packageTitle = adminTrans($package->title ?? ($package->name ?? '')) ?: 'بدون اسم';
        $galleryImages = $package->gallery_images ?? [];

        if (is_string($galleryImages)) {
            $decoded = json_decode($galleryImages, true);
            $galleryImages = is_array($decoded) ? $decoded : [];
        }

        $resolveImagePath = function ($image) {
            if (is_array($image)) {
                return $image['path'] ?? $image['url'] ?? null;
            }

            return $image;
        };
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.index') }}">الرئيسية</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.packages.index') }}">الرحلات</a>
                </li>
                <li class="breadcrumb-item active">عرض الرحلة</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-1">{{ $packageTitle }}</h4>
                    <small class="opacity-75">{{ $package->slug ?? '-' }}</small>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-light">تعديل</a>
                    <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-light">رجوع</a>
                </div>
            </div>

            <div class="profile-body">

                {{-- الصور --}}
                <div class="section-title">الصور والمعرض</div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الصورة الرئيسية</div>

                            @if (!empty($package->featured_image))
                                <img src="{{ asset($package->featured_image) }}" class="image-preview" alt="featured">
                            @else
                                <div class="info-value">-</div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="info-box">
                            <div class="info-label">صور المعرض</div>

                            @if (is_array($galleryImages) && count($galleryImages))
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($galleryImages as $image)
                                        @php($imagePath = $resolveImagePath($image))
                                        @if (!empty($imagePath))
                                            <img src="{{ asset($imagePath) }}" class="image-preview" alt="gallery">
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="info-value">-</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- البيانات الأساسية --}}
                <div class="section-title">البيانات الأساسية</div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">التصنيف</div>
                            <div class="info-value">{{ adminTrans(optional($package->category)->name) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">المدينة</div>
                            <div class="info-value">{{ adminTrans(optional(optional($package->destination)->city)->name) ?: (adminTrans(optional($package->destination)->name) ?: '-') }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الدولة الأساسية</div>
                            <div class="info-value">{{ adminTrans(optional($package->primaryCountry)->name) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">نوع الرحلة</div>
                            <div class="info-value">{{ $package->package_type ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">نوع الجولة</div>
                            <div class="info-value">{{ $package->tour_type ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">نظام الحجز</div>
                            <div class="info-value">{{ $package->booking_mode ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">العملة</div>
                            <div class="info-value">{{ optional($package->currency)->code ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الحالة</div>
                            <div class="info-value">{{ $package->is_active ?? true ? 'مفعلة' : 'غير مفعلة' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">الترتيب</div>
                            <div class="info-value">{{ $package->sort_order ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                {{-- النصوص --}}
                <div class="section-title">النصوص والوصف</div>

                <div class="row">
                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">العنوان الفرعي</div>
                            <div class="info-value">{{ adminTrans($package->subtitle ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">الوصف المختصر</div>
                            <div class="info-value">{{ adminTrans($package->short_description ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">الوصف الكامل</div>
                            <div class="info-value">{{ adminTrans($package->description ?? '') ?: '-' }}</div>
                        </div>
                    </div>
                </div>

                {{-- المدة والمسار --}}
                <div class="section-title">المدة والمسار</div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">عدد الأيام</div>
                            <div class="info-value">{{ $package->duration_days ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">عدد الليالي</div>
                            <div class="info-value">{{ $package->duration_nights ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">نص المدة</div>
                            <div class="info-value">{{ $package->duration_text ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">الجدول</div>
                            <div class="info-value">{{ adminTrans($package->schedule_text ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">المسار</div>
                            <div class="info-value">{{ $package->route_text ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">مكان الاستلام</div>
                            <div class="info-value">{{ adminTrans($package->pickup_location ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">مكان الانتهاء</div>
                            <div class="info-value">{{ adminTrans($package->dropoff_location ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">الوجهات</div>
                            <div class="info-value">{{ adminTrans($package->destinations_text ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">ملخص الموقع</div>
                            <div class="info-value">{{ adminTrans($package->location_summary ?? '') ?: '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="section-title">Trip Facilities</div>

                <div class="info-box">
                    @if (isset($package->packageAttractions) && $package->packageAttractions->count())
                        @foreach ($package->packageAttractions as $packageAttraction)
                            <span class="badge-soft">
                                {{ $packageAttraction->display_title ?: $packageAttraction->attraction?->display_name }}
                            </span>
                        @endforeach
                    @elseif (isset($package->facilities) && $package->facilities->count())
                        @foreach ($package->facilities as $facility)
                            <span class="badge-soft">{{ $facility->title }}</span>
                        @endforeach
                    @else
                        <div class="info-value">-</div>
                    @endif
                </div>

                {{-- البرنامج --}}
                <div class="section-title">برنامج الرحلة</div>

                @if (isset($package->itineraries) && $package->itineraries->count())
                    @foreach ($package->itineraries as $day)
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                <h6 class="mb-0">
                                    Day {{ $day->day_number ?? '-' }} - {{ adminTrans($day->title ?? '') ?: '-' }}
                                </h6>
                                <span class="badge-soft">{{ $day->duration ?? '-' }}</span>
                            </div>

                            <div class="info-value mb-2">{{ adminTrans($day->description ?? '') ?: '-' }}</div>

                            @php
                                $dayMeals = [];
                                if (!empty($day->meals) && (is_array($day->meals) || $day->meals instanceof \Illuminate\Support\Collection)) {
                                    $dayMeals = is_array($day->meals) ? $day->meals : $day->meals->toArray();
                                }
                                if (!empty($day->meals_breakfast) && !in_array('breakfast', $dayMeals)) $dayMeals[] = 'breakfast';
                                if (!empty($day->meals_lunch) && !in_array('lunch', $dayMeals)) $dayMeals[] = 'lunch';
                                if (!empty($day->meals_dinner) && !in_array('dinner', $dayMeals)) $dayMeals[] = 'dinner';
                            @endphp
                            @if (!empty($dayMeals))
                                <div class="meals-included-card mt-3 p-3 rounded-3" style="background-color: #f8f6f0; border-left: 4px solid #c9974c;">
                                    <div class="fw-bold mb-2" style="color: #1e293b; font-size: 0.9rem;">{{ admin_t('Meals Included') }}</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($dayMeals as $m)
                                            @php
                                                $mLower = strtolower((string)$m);
                                                if (in_array($mLower, ['breakfast', 'إفطار', 'افطار'])) {
                                                    $mealText = admin_t('Breakfast');
                                                } elseif (in_array($mLower, ['lunch', 'غداء'])) {
                                                    $mealText = admin_t('Lunch');
                                                } elseif (in_array($mLower, ['dinner', 'عشاء'])) {
                                                    $mealText = admin_t('Dinner');
                                                } else {
                                                    $mealText = admin_t(ucfirst($mLower));
                                                }
                                            @endphp
                                            <span class="badge px-3 py-2 rounded-pill fw-medium" style="background-color: #c9974c; color: #ffffff; font-size: 0.85rem; border: none;">
                                                {{ $mealText }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="info-box">
                        <div class="info-value">-</div>
                    </div>
                @endif

                {{-- المشمول وغير المشمول --}}
                <div class="section-title">المشمول وغير المشمول</div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Included</div>

                            @if (isset($package->inclusions) && $package->inclusions->where('type', 'included')->count())
                                <ul class="mb-0">
                                    @foreach ($package->inclusions->where('type', 'included') as $item)
                                        <li>{{ $item->title }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="info-value">-</div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Not Included</div>

                            @if (isset($package->inclusions) && $package->inclusions->where('type', 'excluded')->count())
                                <ul class="mb-0">
                                    @foreach ($package->inclusions->where('type', 'excluded') as $item)
                                        <li>{{ $item->title }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="info-value">-</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- الأسعار --}}
                <div class="section-title">الأسعار</div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">سعر البالغ</div>
                            <div class="info-value">
                                {{ number_format((float) ($package->adult_price ?? 0), 2) }}
                                {{ optional($package->currency)->code }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">سعر الطفل</div>
                            <div class="info-value">
                                {{ number_format((float) ($package->child_price ?? 0), 2) }}
                                {{ optional($package->currency)->code }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">سعر الرضيع</div>
                            <div class="info-value">
                                {{ number_format((float) ($package->infant_price ?? 0), 2) }}
                                {{ optional($package->currency)->code }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">سعر المقارنة</div>
                            <div class="info-value">
                                {{ $package->compare_price ? number_format($package->compare_price, 2) : '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">النطاق السعري المعروض</div>
                            <div class="info-value">
                                {{ number_format((float) ($package->price_from ?? 0), 2) }}
                                {{ optional($package->currency)->code }} -
                                {{ number_format((float) ($package->price_to ?? 0), 2) }}
                                {{ optional($package->currency)->code }}
                            </div>
                        </div>
                    </div>
                </div>

                @if (isset($package->prices) && $package->prices->count())
                    <div class="table-responsive">
                        <table class="table table-dark-custom">
                            <thead>
                                <tr>
                                    <th>Label</th>
                                    <th>Season</th>
                                    <th>Price Type</th>
                                    <th>Room</th>
                                    <th>عدد الأفراد</th>
                                    <th>Amount</th>
                                    <th>Currency</th>
                                    <th>Valid From</th>
                                    <th>Valid To</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($package->prices as $price)
                                    <tr>
                                        <td>{{ adminTrans($price->label ?? '') ?: '-' }}</td>
                                        <td>{{ adminTrans($price->season_name ?? '') ?: '-' }}</td>
                                        <td>{{ $price->price_type ?? '-' }}</td>
                                        <td>{{ $price->room_type ?? '-' }}</td>
                                        <td>
                                            @if ($price->pax_min && $price->pax_max && $price->pax_min === $price->pax_max)
                                                {{ $price->pax_min }}
                                            @elseif ($price->pax_min && $price->pax_max)
                                                {{ $price->pax_min }} - {{ $price->pax_max }}
                                            @elseif ($price->pax_min)
                                                {{ $price->pax_min }}+
                                            @elseif ($price->pax_max)
                                                1 - {{ $price->pax_max }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ number_format($price->amount ?? 0, 2) }}</td>
                                        <td>{{ optional($price->currency)->code ?? (optional($package->currency)->code ?? '-') }}
                                        </td>
                                        <td>{{ $price->valid_from ?? '-' }}</td>
                                        <td>{{ $price->valid_to ?? '-' }}</td>
                                        <td>{{ adminTrans($price->notes ?? '') ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="info-box">
                        <div class="info-value">-</div>
                    </div>
                @endif

                <div class="info-box mt-3">
                    <div class="info-label">ملاحظات الأسعار</div>
                    <div class="info-value">{{ adminTrans($package->pricing_information ?? '') ?: '-' }}</div>
                </div>

                {{-- السياسات --}}
                <div class="section-title">السياسات والشروط</div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="info-box">
                            <div class="info-label">{{ __('trips.age_policy') }}</div>
                            <div class="info-value">
                                البالغون: {{ (int) ($package->adult_min_age ?? 12) }}+<br>
                                الأطفال: {{ (int) ($package->child_min_age ?? 2) }} - {{ (int) ($package->child_max_age ?? 11) }}<br>
                                الرضع: {{ (int) ($package->infant_min_age ?? 0) }} - {{ (int) ($package->infant_max_age ?? 1) }}
                            </div>
                        </div>
                    </div>
                </div>

                @if (!empty($package->faq_json) && is_array($package->faq_json))
                    <div class="section-title">الأسئلة الشائعة</div>

                    @foreach ($package->faq_json as $faq)
                        <div class="info-box">
                            <div class="info-label">
                                {{ is_array($faq['question'] ?? null) ? ($faq['question'][app()->getLocale()] ?? $faq['question']['en'] ?? '-') : ($faq['question'] ?? '-') }}
                            </div>
                            <div class="info-value">
                                {{ is_array($faq['answer'] ?? null) ? ($faq['answer'][app()->getLocale()] ?? $faq['answer']['en'] ?? '-') : ($faq['answer'] ?? '-') }}
                            </div>
                        </div>
                    @endforeach
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">سياسة الأطفال</div>
                            <div class="info-value">{{ adminTrans($package->children_policy ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">سياسة الاستلام والتوصيل</div>
                            <div class="info-value">{{ adminTrans($package->pickup_policy ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">سياسة الإلغاء</div>
                            <div class="info-value">{{ adminTrans($package->cancellation_policy ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">الشروط والأحكام</div>
                            <div class="info-value">{{ adminTrans($package->terms_conditions ?? '') ?: '-' }}</div>
                        </div>
                    </div>
                </div>

                {{-- المشاركون والتقييم --}}
                <div class="section-title">المشاركون والتقييم</div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">الحد الأدنى للمشاركين</div>
                            <div class="info-value">{{ $package->min_participants ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">الحد الأقصى للمشاركين</div>
                            <div class="info-value">{{ $package->max_participants ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">أيام الحجز المسبق</div>
                            <div class="info-value">{{ $package->booking_lead_days ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">التقييم</div>
                            <div class="info-value">{{ $package->rating_avg ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">عدد المراجعات</div>
                            <div class="info-value">{{ $package->reviews_count ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">مستوى الصعوبة</div>
                            <div class="info-value">{{ $package->difficulty_level ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">رابط الفيديو</div>
                            <div class="info-value">
                                @if (!empty($package->video_url))
                                    <a href="{{ $package->video_url }}" target="_blank" class="text-white">
                                        {{ $package->video_url }}
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- النشر --}}
                <div class="section-title">النشر والإعدادات</div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">تاريخ النشر</div>
                            <div class="info-value">{{ optional($package->published_at)->format('Y-m-d') ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">مميزة</div>
                            <div class="info-value">{{ $package->is_featured ? 'نعم' : 'لا' }}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">الأكثر مبيعًا</div>
                            <div class="info-value">{{ $package->is_best_seller ? 'نعم' : 'لا' }}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="info-box">
                            <div class="info-label">فاخرة جدًا</div>
                            <div class="info-value">{{ $package->is_ultra_luxury ? 'نعم' : 'لا' }}</div>
                        </div>
                    </div>
                </div>

                {{-- SEO --}}
                <div class="section-title">SEO</div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">SEO Title</div>
                            <div class="info-value">{{ adminTrans($package->seo_title ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Breadcrumb Title</div>
                            <div class="info-value">{{ adminTrans($package->breadcrumb_title ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="info-box">
                            <div class="info-label">SEO Description</div>
                            <div class="info-value">{{ adminTrans($package->seo_description ?? '') ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Canonical URL</div>
                            <div class="info-value">{{ $package->canonical_url ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                @if (Route::has('admin.package-prices.by-package'))
                    <div class="mt-3">
                        <a href="{{ route('admin.package-prices.by-package', $package) }}" class="btn btn-primary">
                            عرض أسعار الباقة
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection

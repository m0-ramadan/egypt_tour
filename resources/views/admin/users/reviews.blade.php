@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('تقييمات المستخدم') . ': ' . $user->name)

@section('css')

    <style>
        body {
            font-family: "Cairo", sans-serif !important;
        }

        .review-card {
            /* background: white; */
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .review-header {
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .review-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .review-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .review-header-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .review-product {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-image {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
        }

        .product-info h6 {
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }

        .product-info p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
        }

        .review-rating {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .review-rating .stars {
            color: #ffc107;
        }

        .review-rating .rating-number {
            font-weight: 600;
            color: #2c3e50;
        }

        .review-date {
            font-size: 12px;
            color: #6c757d;
        }

        .review-content {
            margin-bottom: 15px;
        }

        .review-comment {
            color: #495057;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .review-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
        }

        .empty-state-icon {
            font-size: 60px;
            color: #dee2e6;
            margin-bottom: 20px;
        }

        .empty-state-text {
            color: #6c757d;
            margin-bottom: 20px;
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-top: 4px solid #696cff;
            margin-bottom: 20px;
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

        .icon-reviews {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .icon-average {
            background: #e7f5ff;
            color: #0c63e4;
        }

        .icon-high {
            background: #d4edda;
            color: #155724;
        }

        .icon-low {
            background: #fff3cd;
            color: #856404;
        }

        .stats-number {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stats-label {
            color: #6c757d;
            font-size: 14px;
        }

        .user-info-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border-right: 4px solid #696cff;
        }

        @media (max-width: 768px) {
            .review-header-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .review-product {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y" bis_skin_checked="1">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.index') }}">الرئيسية</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.users.index') }}">المستخدمين</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a>
                </li>
                <li class="breadcrumb-item active">التقييمات</li>
            </ol>
        </nav>

        <!-- معلومات المستخدم -->
        <div class="user-info-card" bis_skin_checked="1">
            <div class="row align-items-center" bis_skin_checked="1">
                <div class="col" bis_skin_checked="1">
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="mb-0">
                        <i class="fas fa-envelope me-2 text-muted"></i>
                        {{ $user->email }}
                    </p>
                </div>
                <div class="col-auto" bis_skin_checked="1">
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة للتفاصيل
                    </a>
                </div>
            </div>
        </div>

        <!-- الإحصائيات -->
        <div class="row mb-4" bis_skin_checked="1">
            <div class="col-lg-3 col-md-6 mb-4" bis_skin_checked="1">
                <div class="stats-card" bis_skin_checked="1">
                    <div class="stats-icon icon-reviews" bis_skin_checked="1">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stats-number" bis_skin_checked="1">
                        {{ $reviews->total() }}
                    </div>
                    <div class="stats-label" bis_skin_checked="1">إجمالي التقييمات</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4" bis_skin_checked="1">
                @php
                    $averageRating = $reviews->avg('rating') ?? 0;
                @endphp
                <div class="stats-card" bis_skin_checked="1">
                    <div class="stats-icon icon-average" bis_skin_checked="1">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stats-number" bis_skin_checked="1">
                        {{ number_format($averageRating, 1) }}
                    </div>
                    <div class="stats-label" bis_skin_checked="1">متوسط التقييم</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4" bis_skin_checked="1">
                @php
                    $highRatings = $reviews->where('rating', '>=', 4)->count();
                @endphp
                <div class="stats-card" bis_skin_checked="1">
                    <div class="stats-icon icon-high" bis_skin_checked="1">
                        <i class="fas fa-thumbs-up"></i>
                    </div>
                    <div class="stats-number" bis_skin_checked="1">
                        {{ $highRatings }}
                    </div>
                    <div class="stats-label" bis_skin_checked="1">تقييمات عالية (4+)</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4" bis_skin_checked="1">
                @php
                    $lowRatings = $reviews->where('rating', '<=', 2)->count();
                @endphp
                <div class="stats-card" bis_skin_checked="1">
                    <div class="stats-icon icon-low" bis_skin_checked="1">
                        <i class="fas fa-thumbs-down"></i>
                    </div>
                    <div class="stats-number" bis_skin_checked="1">
                        {{ $lowRatings }}
                    </div>
                    <div class="stats-label" bis_skin_checked="1">تقييمات منخفضة (2-)</div>
                </div>
            </div>
        </div>

        <div class="row" bis_skin_checked="1">
            <div class="col-12" bis_skin_checked="1">
                <div class="review-card" bis_skin_checked="1">
                    <div class="review-header" bis_skin_checked="1">
                        <div class="d-flex justify-content-between align-items-center" bis_skin_checked="1">
                            <div bis_skin_checked="1">
                                <h5 class="mb-1">تقييمات المستخدم</h5>
                                <p class="text-muted mb-0">عرض جميع تقييمات {{ $user->name }}</p>
                            </div>
                            <div class="btn-group" bis_skin_checked="1">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-info">
                                    <i class="fas fa-user me-2"></i>تفاصيل المستخدم
                                </a>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-right me-2"></i>جميع المستخدمين
                                </a>
                            </div>
                        </div>
                    </div>

                    @if ($reviews->isEmpty())
                        <div class="empty-state" bis_skin_checked="1">
                            <div class="empty-state-icon" bis_skin_checked="1">
                                <i class="fas fa-star"></i>
                            </div>
                            <h5 class="empty-state-text">لا توجد تقييمات لهذا المستخدم</h5>
                            <p class="text-muted">المستخدم لم يقم بتقييم أي منتجات حتى الآن</p>
                        </div>
                    @else
                        @foreach ($reviews as $review)
                            <div class="review-item" bis_skin_checked="1">
                                <div class="review-header-info" bis_skin_checked="1">
                                    <div class="review-product" bis_skin_checked="1">
                                        @if ($review->product && $review->product->image)
                                            <img src="{{ asset('storage/' . $review->product->image) }}"
                                                alt="{{ $review->product->name }}" class="product-image">
                                        @else
                                            <div class="product-image"
                                                style="background: #dee2e6; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-box text-muted"></i>
                                            </div>
                                        @endif
                                        <div class="product-info" bis_skin_checked="1">
                                            <h6>{{ $review->product->name ?? 'منتج محذوف' }}</h6>
                                            <p>{{ $review->product->category->name ?? 'غير مصنف' }}</p>
                                        </div>
                                    </div>

                                    <div class="review-rating" bis_skin_checked="1">
                                        <div class="stars" bis_skin_checked="1">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i
                                                    class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                            @endfor
                                        </div>
                                        <span class="rating-number">{{ $review->rating }}/5</span>
                                    </div>
                                </div>

                                <div class="review-content" bis_skin_checked="1">
                                    <div class="review-comment" bis_skin_checked="1">
                                        {{ $review->comment }}
                                    </div>
                                    <div class="review-date" bis_skin_checked="1">
                                        <i class="far fa-clock me-1"></i>
                                        {{ $review->created_at->translatedFormat('d M Y - h:i A') }}
                                    </div>
                                </div>

                                @if ($review->product)
                                    <div class="review-actions" bis_skin_checked="1">
                                        <a href="{{ route('admin.products.show', $review->product->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>عرض المنتج
                                        </a>
                                        <a href="{{ route('admin.reviews.edit', $review) }}"
                                            class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-edit me-1"></i>تعديل التقييم
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if ($reviews->hasPages())
                            <div class="mt-4" bis_skin_checked="1">
                                <div class="d-flex justify-content-center" bis_skin_checked="1">
                                    {{ $reviews->links() }}
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

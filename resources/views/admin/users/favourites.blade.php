@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('المفضلة') . ': ' . $user->name)

@section('css')

    <style>
        body {
            font-family: "Cairo", sans-serif !important;
        }

        .favourites-card {
            /* background: white; */
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .favourites-header {
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .product-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            width: 100%;
            height: 180px;
            border-radius: 10px;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .product-image-placeholder {
            width: 100%;
            height: 180px;
            border-radius: 10px;
            background: #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            color: #6c757d;
            font-size: 48px;
        }

        .product-info h6 {
            margin-bottom: 10px;
            font-weight: 600;
            color: #2c3e50;
            height: 45px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .product-category {
            color: #6c757d;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .product-price {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .current-price {
            font-size: 18px;
            font-weight: 700;
            color: #2ecc71;
        }

        .old-price {
            font-size: 14px;
            color: #95a5a6;
            text-decoration: line-through;
        }

        .product-actions {
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

        .icon-favourites {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .icon-categories {
            background: #e7f5ff;
            color: #0c63e4;
        }

        .icon-price {
            background: #d4edda;
            color: #155724;
        }

        .icon-recent {
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
            .product-card {
                margin-bottom: 20px;
            }

            .product-actions {
                flex-direction: column;
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
                <li class="breadcrumb-item active">المفضلة</li>
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
                    <div class="stats-icon icon-favourites" bis_skin_checked="1">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stats-number" bis_skin_checked="1">
                        {{ $favourites->total() }}
                    </div>
                    <div class="stats-label" bis_skin_checked="1">إجمالي المنتجات المفضلة</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4" bis_skin_checked="1">
                @php
                    $categoriesCount = $favourites->unique('category_id')->count();
                @endphp
                <div class="stats-card" bis_skin_checked="1">
                    <div class="stats-icon icon-categories" bis_skin_checked="1">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stats-number" bis_skin_checked="1">
                        {{ $categoriesCount }}
                    </div>
                    <div class="stats-label" bis_skin_checked="1">عدد الأقسام المختلفة</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4" bis_skin_checked="1">
                @php
                    $averagePrice = $favourites->avg('final_price') ?? 0;
                @endphp
                <div class="stats-card" bis_skin_checked="1">
                    <div class="stats-icon icon-price" bis_skin_checked="1">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stats-number" bis_skin_checked="1">
                        {{ number_format($averagePrice, 2) }} ج.م
                    </div>
                    <div class="stats-label" bis_skin_checked="1">متوسط سعر المنتجات</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4" bis_skin_checked="1">
                @php
                    $recentFavourites = $favourites->where('pivot.created_at', '>=', now()->subDays(7))->count();
                @endphp
                <div class="stats-card" bis_skin_checked="1">
                    <div class="stats-icon icon-recent" bis_skin_checked="1">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-number" bis_skin_checked="1">
                        {{ $recentFavourites }}
                    </div>
                    <div class="stats-label" bis_skin_checked="1">مضاف حديثاً (أسبوع)</div>
                </div>
            </div>
        </div>

        <div class="row" bis_skin_checked="1">
            <div class="col-12" bis_skin_checked="1">
                <div class="favourites-card" bis_skin_checked="1">
                    <div class="favourites-header" bis_skin_checked="1">
                        <div class="d-flex justify-content-between align-items-center" bis_skin_checked="1">
                            <div bis_skin_checked="1">
                                <h5 class="mb-1">المنتجات المفضلة</h5>
                                <p class="text-muted mb-0">عرض جميع منتجات {{ $user->name }} المفضلة</p>
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

                    @if ($favourites->isEmpty())
                        <div class="empty-state" bis_skin_checked="1">
                            <div class="empty-state-icon" bis_skin_checked="1">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h5 class="empty-state-text">لا توجد منتجات مفضلة لهذا المستخدم</h5>
                            <p class="text-muted">المستخدم لم يضف أي منتجات إلى المفضلة حتى الآن</p>
                        </div>
                    @else
                        <div class="row" bis_skin_checked="1">
                            @foreach ($favourites as $product)
                                <div class="col-lg-4 col-md-6 mb-4" bis_skin_checked="1">
                                    <div class="product-card" bis_skin_checked="1">
                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}"
                                                alt="{{ $product->name }}" class="product-image">
                                        @else
                                            <div class="product-image-placeholder" bis_skin_checked="1">
                                                <i class="fas fa-box"></i>
                                            </div>
                                        @endif

                                        <div class="product-info" bis_skin_checked="1">
                                            <h6>{{ $product->name }}</h6>

                                            <div class="product-category" bis_skin_checked="1">
                                                <i class="fas fa-tag me-1"></i>
                                                {{ $product->category->name ?? 'غير مصنف' }}
                                            </div>

                                            <div class="product-price" bis_skin_checked="1">
                                                <span class="current-price">
                                                    {{ number_format($product->final_price, 2) }} ج.م
                                                </span>
                                                @if ($product->has_discount && $product->price > $product->final_price)
                                                    <span class="old-price">
                                                        {{ number_format($product->price, 2) }} ج.م
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mb-2"
                                                bis_skin_checked="1">
                                                <div class="text-muted" bis_skin_checked="1">
                                                    <i class="fas fa-star text-warning me-1"></i>
                                                    {{ number_format($product->average_rating, 1) }}
                                                </div>
                                                <div class="text-muted" bis_skin_checked="1">
                                                    <i class="fas fa-box me-1"></i>
                                                    {{ $product->stock }}
                                                </div>
                                            </div>

                                            <div class="product-actions" bis_skin_checked="1">
                                                <a href="{{ route('admin.products.show', $product->id) }}"
                                                    class="btn btn-sm btn-outline-primary flex-grow-1">
                                                    <i class="fas fa-eye me-1"></i>عرض
                                                </a>
                                                <a href="{{ route('admin.products.edit', $product) }}"
                                                    class="btn btn-sm btn-outline-warning flex-grow-1">
                                                    <i class="fas fa-edit me-1"></i>تعديل
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($favourites->hasPages())
                            <div class="mt-4" bis_skin_checked="1">
                                <div class="d-flex justify-content-center" bis_skin_checked="1">
                                    {{ $favourites->links() }}
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

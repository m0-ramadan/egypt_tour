@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('طلبات المستخدم') . ': ' . $user->name)

@section('css')

    <style>
        body {
            font-family: "Cairo", sans-serif !important;
        }

        .order-card {
            /* background: white; */
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .order-header {
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .user-info-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border-right: 4px solid #696cff;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f8f9fa;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .user-avatar-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 600;
            border: 3px solid #f8f9fa;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .badge-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #cce5ff;
            color: #004085;
        }

        .status-shipped {
            background: #d4edda;
            color: #155724;
        }

        .status-delivered {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .status-refunded {
            background: #e2e3e5;
            color: #383d41;
        }

        .order-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .order-item:hover {
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .order-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .order-item-title {
            font-weight: 600;
            color: #2c3e50;
        }

        .order-item-date {
            font-size: 12px;
            color: #6c757d;
        }

        .order-item-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-label {
            font-weight: 600;
            color: #495057;
            min-width: 80px;
        }

        .detail-value {
            color: #2c3e50;
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

        .icon-orders {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .icon-total {
            background: #e7f5ff;
            color: #0c63e4;
        }

        .icon-pending {
            background: #fff3cd;
            color: #856404;
        }

        .icon-delivered {
            background: #d4edda;
            color: #155724;
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

        @media (max-width: 768px) {
            .order-item-details {
                grid-template-columns: 1fr;
            }

            .order-item-header {
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
                <li class="breadcrumb-item active">الطلبات</li>
            </ol>
        </nav>

        <!-- معلومات المستخدم -->
        <div class="user-info-card" bis_skin_checked="1">
            <div class="row align-items-center" bis_skin_checked="1">
                <div class="col-auto" bis_skin_checked="1">
                    @if ($user->image)
                        <img src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}" class="user-avatar">
                    @else
                        <div class="user-avatar-placeholder" bis_skin_checked="1">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="col" bis_skin_checked="1">
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="mb-1">
                        <i class="fas fa-envelope me-2 text-muted"></i>
                        {{ $user->email }}
                    </p>
                    @if ($user->phone)
                        <p class="mb-0">
                            <i class="fas fa-phone me-2 text-muted"></i>
                            {{ $user->phone }}
                        </p>
                    @endif
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
                    <div class="stats-icon icon-orders" bis_skin_checked="1">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stats-number" bis_skin_checked="1">
                        {{ $orders->total() }}
                    </div>
                    <div class="stats-label" bis_skin_checked="1">إجمالي الطلبات</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4" bis_skin_checked="1">
                @php
                    $totalAmount = $orders->sum('total_amount');
                @endphp
                <div class="stats-card" bis_skin_checked="1">
                    <div class="stats-icon icon-total" bis_skin_checked="1">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stats-number" bis_skin_checked="1">
                        {{ number_format($totalAmount, 2) }} ج.م
                    </div>
                    <div class="stats-label" bis_skin_checked="1">إجمالي القيمة</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4" bis_skin_checked="1">
                @php
                    $pendingOrders = $orders->where('status', 'pending')->count();
                @endphp
                <div class="stats-card" bis_skin_checked="1">
                    <div class="stats-icon icon-pending" bis_skin_checked="1">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-number" bis_skin_checked="1">
                        {{ $pendingOrders }}
                    </div>
                    <div class="stats-label" bis_skin_checked="1">طلبات قيد الانتظار</div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4" bis_skin_checked="1">
                @php
                    $deliveredOrders = $orders->where('status', 'delivered')->count();
                @endphp
                <div class="stats-card" bis_skin_checked="1">
                    <div class="stats-icon icon-delivered" bis_skin_checked="1">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number" bis_skin_checked="1">
                        {{ $deliveredOrders }}
                    </div>
                    <div class="stats-label" bis_skin_checked="1">طلبات مكتملة</div>
                </div>
            </div>
        </div>

        <div class="row" bis_skin_checked="1">
            <div class="col-12" bis_skin_checked="1">
                <div class="order-card" bis_skin_checked="1">
                    <div class="order-header" bis_skin_checked="1">
                        <div class="d-flex justify-content-between align-items-center" bis_skin_checked="1">
                            <div bis_skin_checked="1">
                                <h5 class="mb-1">طلبات المستخدم</h5>
                                <p class="text-muted mb-0">عرض جميع طلبات {{ $user->name }}</p>
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

                    @if ($orders->isEmpty())
                        <div class="empty-state" bis_skin_checked="1">
                            <div class="empty-state-icon" bis_skin_checked="1">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h5 class="empty-state-text">لا توجد طلبات لهذا المستخدم</h5>
                            <p class="text-muted">المستخدم لم يقم بأي طلبات حتى الآن</p>
                        </div>
                    @else
                        @foreach ($orders as $order)
                            <div class="order-item" bis_skin_checked="1">
                                <div class="order-item-header" bis_skin_checked="1">
                                    <div class="order-item-title" bis_skin_checked="1">
                                        <div class="d-flex align-items-center gap-3" bis_skin_checked="1">
                                            <span>الطلب #{{ $order->order_number }}</span>
                                            <span class="badge-status status-{{ $order->status }}">
                                                {{ $order->status_label }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="order-item-date" bis_skin_checked="1">
                                        {{ $order->created_at->translatedFormat('d M Y - h:i A') }}
                                    </div>
                                </div>

                                <div class="order-item-details" bis_skin_checked="1">
                                    <div class="detail-item" bis_skin_checked="1">
                                        <span class="detail-label">القيمة:</span>
                                        <span class="detail-value">
                                            {{ number_format($order->total_amount, 2) }} ج.م
                                        </span>
                                    </div>

                                    <div class="detail-item" bis_skin_checked="1">
                                        <span class="detail-label">العنوان:</span>
                                        <span class="detail-value">
                                            {{ Str::limit($order->shipping_address, 50) }}
                                        </span>
                                    </div>

                                    <div class="detail-item" bis_skin_checked="1">
                                        <span class="detail-label">طريقة الدفع:</span>
                                        <span class="detail-value">
                                            {{ $order->payment_method }}
                                        </span>
                                    </div>

                                    <div class="detail-item" bis_skin_checked="1">
                                        <span class="detail-label">عدد المنتجات:</span>
                                        <span class="detail-value">
                                            {{ $order->items->count() }} منتج
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-3" bis_skin_checked="1">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-2"></i>عرض تفاصيل الطلب
                                    </a>
                                </div>
                            </div>
                        @endforeach

                        @if ($orders->hasPages())
                            <div class="mt-4" bis_skin_checked="1">
                                <div class="d-flex justify-content-center" bis_skin_checked="1">
                                    {{ $orders->links() }}
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

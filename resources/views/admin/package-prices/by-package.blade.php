@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('أسعار الباقة'))

@section('css')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
            --info-color: #0c63e4;
        }

        body {
            font-family: "Cairo", sans-serif !important;
            background: var(--dark-bg);
            color: #fff;
        }

        .main-card {
            background: var(--dark-card);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .main-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 25px 30px;
        }

        .content-body {
            padding: 30px;
        }

        .item-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .badge-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            background: rgba(12, 99, 228, .2);
            color: var(--info-color);
            border: 1px solid rgba(12, 99, 228, .3);
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.packages.index') }}">الباقات</a></li>
                <li class="breadcrumb-item active">أسعار الباقة</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">أسعار الباقة</h5>
                    <small class="opacity-75">{{ adminTrans($package->title ?? ($package->name ?? '')) ?: '-' }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.package-prices.create', ['package_id' => $package->id]) }}"
                        class="btn btn-light">إضافة سعر</a>
                    <a href="{{ route('admin.packages.show', $package) }}" class="btn btn-light">رجوع</a>
                </div>
            </div>

            <div class="content-body">
                @forelse($packagePrices as $price)
                    <div class="item-card">
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                            <div>
                                <h6 class="mb-1">{{ adminTrans($price->label ?? '') ?: '-' }}</h6>
                                <small class="text-light opacity-75">{{ adminTrans($price->season_name ?? '') ?: '-' }}</small>
                            </div>

                            <span class="badge-status">
                                {{ number_format($price->amount ?? 0, 2) }} {{ $price->currency->code ?? '-' }}
                            </span>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Type:</strong> {{ $price->price_type ?? '-' }}</div>
                            <div class="col-md-3"><strong>Room:</strong> {{ $price->room_type ?? '-' }}</div>
                            <div class="col-md-3"><strong>Pax:</strong> {{ $price->pax_min ?? '-' }} -
                                {{ $price->pax_max ?? '-' }}</div>
                            <div class="col-md-3"><strong>Group:</strong> {{ $price->group_size_min ?? '-' }} -
                                {{ $price->group_size_max ?? '-' }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Valid From:</strong>
                                {{ optional($price->valid_from)->translatedFormat('d M Y') ?? '-' }}</div>
                            <div class="col-md-6"><strong>Valid To:</strong>
                                {{ optional($price->valid_to)->translatedFormat('d M Y') ?? '-' }}</div>
                        </div>

                        <div class="mb-3">
                            <strong>Notes:</strong> {{ adminTrans($price->notes ?? '') ?: '-' }}
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.package-prices.show', $price) }}" class="btn btn-info btn-sm">عرض</a>
                            <a href="{{ route('admin.package-prices.edit', $price) }}"
                                class="btn btn-warning btn-sm">تعديل</a>
                            <form action="{{ route('admin.package-prices.destroy', $price) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">حذف</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">لا توجد أسعار لهذه الباقة</div>
                @endforelse

                @if (method_exists($packagePrices, 'links'))
                    <div class="mt-4">{{ $packagePrices->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection

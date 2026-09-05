@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('سجل تواصل الاستفسار'))

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
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.communications.index') }}">سجل التواصل</a></li>
                <li class="breadcrumb-item active">تواصل الاستفسار</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header">
                <h5 class="mb-0">سجل تواصل الاستفسار</h5>
                <small class="opacity-75">
                    {{ $inquiry->subject ?? ($communication->subject ?? '-') }}
                </small>
            </div>

            <div class="content-body">
                @forelse($communications ?? collect([$communication]) as $item)
                    <div class="item-card">
                        <h6 class="mb-2">{{ $item->subject ?? 'بدون عنوان' }}</h6>
                        <div class="mb-2"><strong>القناة:</strong> {{ $item->channel ?? '-' }}</div>
                        <div class="mb-2"><strong>التاريخ:</strong>
                            {{ optional($item->created_at)->translatedFormat('d M Y - h:i A') ?? '-' }}</div>
                        <div><strong>الرسالة:</strong> {{ $item->message ?? '-' }}</div>
                    </div>
                @empty
                    <div class="text-center py-5">لا توجد سجلات لهذا الاستفسار</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('عناصر القائمة'))

@section('css')
    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-color: #20c997;
            --danger-color: #fd7e14;
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

        .section-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, .08);
            margin-bottom: 20px;
        }

        .item-card {
            background: rgba(255, 255, 255, .04);
            border-radius: 10px;
            padding: 18px;
            border: 1px solid rgba(255, 255, 255, .08);
            margin-bottom: 15px;
        }

        .form-control,
        .form-select,
        textarea {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            color: #fff;
            border-radius: 10px;
            min-height: 46px;
        }

        .form-control:focus,
        .form-select:focus,
        textarea:focus {
            background: rgba(255, 255, 255, .08);
            color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 .25rem rgba(105, 108, 255, .25);
        }

        .nested-badge {
            background: rgba(12, 99, 228, .2);
            color: #0c63e4;
            border: 1px solid rgba(12, 99, 228, .3);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.menus.index') }}">القوائم</a></li>
                <li class="breadcrumb-item active">عناصر القائمة</li>
            </ol>
        </nav>

        <div class="main-card">
            <div class="main-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">عناصر القائمة: {{ adminTrans($menu->name) ?: '-' }}</h5>
                    <small class="opacity-75">إدارة روابط وعناصر القائمة</small>
                </div>
                <a href="{{ route('admin.menus.index') }}" class="btn btn-light">رجوع</a>
            </div>

            <div class="p-4">

                <div class="section-card">
                    <h5 class="mb-3">إضافة عنصر جديد</h5>

                    <form action="{{ route('admin.menu-items.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="menu_id" value="{{ $menu->id }}">

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">العنوان</label>
                                <input type="text" name="title" class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">الرابط</label>
                                <input type="text" name="url" class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Parent Item</label>
                                <select name="parent_id" class="form-select">
                                    <option value="">بدون</option>
                                    @foreach ($menu->items ?? collect() as $parentItem)
                                        <option value="{{ $parentItem->id }}">{{ adminTrans($parentItem->title) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">الترتيب</label>
                                <input type="number" name="sort_order" class="form-control" value="0">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">الهدف</label>
                                <select name="target" class="form-select">
                                    <option value="_self">_self</option>
                                    <option value="_blank">_blank</option>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">الأيقونة</label>
                                <input type="text" name="icon" class="form-control">
                            </div>

                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" value="1" name="is_active"
                                        id="is_active" checked>
                                    <label class="form-check-label" for="is_active">مفعل</label>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit">إضافة العنصر</button>
                    </form>
                </div>


                <div class="section-card">
                    <h5 class="mb-3">العناصر الحالية</h5>

                    @forelse($menu->items ?? [] as $item)
                        <div class="item-card">
                            <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                                <div>
                                    <h6 class="mb-1">{{ adminTrans($item->title) ?: '-' }}</h6>
                                    <small class="text-light opacity-75">{{ $item->url ?? '-' }}</small>
                                </div>

                                @if ($item->parent_id)
                                    <span class="nested-badge">عنصر فرعي</span>
                                @endif
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3"><strong>الترتيب:</strong> {{ $item->sort_order ?? 0 }}</div>
                                <div class="col-md-3"><strong>الهدف:</strong> {{ $item->target ?? '_self' }}</div>
                                <div class="col-md-3"><strong>الأيقونة:</strong> {{ $item->icon ?? '-' }}</div>
                                <div class="col-md-3"><strong>الحالة:</strong>
                                    {{ $item->is_active ?? true ? 'مفعل' : 'غير مفعل' }}</div>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                @if (Route::has('admin.menu-items.edit'))
                                    <a href="{{ route('admin.menu-items.edit', $item) }}"
                                        class="btn btn-warning btn-sm">تعديل</a>
                                @endif

                                @if (Route::has('admin.menu-items.destroy'))
                                    <form action="{{ route('admin.menu-items.destroy', $item) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" type="submit">حذف</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">لا توجد عناصر داخل هذه القائمة</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

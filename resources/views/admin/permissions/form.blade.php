@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t($title))

@section('css')

    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
        }

        body {
            font-family: "Cairo", sans-serif !important;
            background: var(--dark-bg);
            color: #fff;
        }

        .form-card {
            background: var(--dark-card);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-header {
            background: var(--primary-gradient);
            color: white;
            padding: 25px 30px;
            border-radius: 15px 15px 0 0;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-color);
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25);
        }

        .form-text {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 5px;
        }

        .error-message {
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4a9a 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(105, 108, 255, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .module-info {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .info-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            background: var(--primary-gradient);
            color: white;
            margin-bottom: 15px;
        }

        .info-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            line-height: 1.6;
        }

        .permission-name-preview {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 10px 15px;
            margin-top: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-family: monospace;
            color: rgba(255, 255, 255, 0.9);
        }

        .example-box {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            border-left: 4px solid var(--primary-color);
        }

        .example-title {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 5px;
        }

        .example-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            font-family: monospace;
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
                    <a href="{{ route('admin.permissions.index') }}">الصلاحيات</a>
                </li>
                <li class="breadcrumb-item active">{{ $title }}</li>
            </ol>
        </nav>

        <div class="row" bis_skin_checked="1">
            <div class="col-12" bis_skin_checked="1">
                <div class="form-card" bis_skin_checked="1">
                    <div class="form-header" bis_skin_checked="1">
                        <div class="d-flex justify-content-between align-items-center" bis_skin_checked="1">
                            <div bis_skin_checked="1">
                                <h5 class="mb-1">{{ $title }}</h5>
                                <small class="opacity-75">{{ $description }}</small>
                            </div>
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-light">
                                <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
                            </a>
                        </div>
                    </div>

                    <form action="{{ $action }}" method="POST">
                        @csrf
                        @if (isset($permission) && $permission->id)
                            @method('PUT')
                        @endif

                        <div class="row" bis_skin_checked="1">
                            <div class="col-lg-8" bis_skin_checked="1">
                                <!-- معلومات الصلاحية -->
                                <div class="mb-4" bis_skin_checked="1">
                                    <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>معلومات الصلاحية</h6>

                                    <div class="form-group" bis_skin_checked="1">
                                        <label class="form-label">الوحدة *</label>
                                        <select name="module" id="module"
                                            class="form-control @error('module') is-invalid @enderror" required>
                                            <option value="">اختر الوحدة</option>
                                            @foreach ($modules as $key => $value)
                                                <option value="{{ $key }}"
                                                    @if (old('module', $module ?? '') == $key) selected @endif>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text" bis_skin_checked="1">
                                            الوحدة التي تنتمي إليها الصلاحية (مثال: users, products)
                                        </div>
                                        @error('module')
                                            <div class="error-message">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group" bis_skin_checked="1">
                                        <label class="form-label">الإجراء *</label>
                                        <select name="action" id="action"
                                            class="form-control @error('action') is-invalid @enderror" required>
                                            <option value="">اختر الإجراء</option>
                                            @foreach ($permissionTypes as $key => $value)
                                                <option value="{{ $key }}"
                                                    @if (old('action', $actionVal ?? '') == $key) selected @endif>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text" bis_skin_checked="1">
                                            نوع الإجراء الذي تسمح به الصلاحية (مثال: view, create, edit)
                                        </div>
                                        @error('action')
                                            <div class="error-message">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group" bis_skin_checked="1">
                                        <label class="form-label">الاسم المعروض (عربي) *</label>
                                        <input type="text" name="display_name"
                                            class="form-control @error('display_name') is-invalid @enderror"
                                            value="{{ old('display_name', $permission->display_name ?? '') }}"
                                            placeholder="مثال: عرض المستخدمين" required>
                                        <div class="form-text" bis_skin_checked="1">
                                            الاسم الذي سيعرض للمستخدمين باللغة العربية
                                        </div>
                                        @error('display_name')
                                            <div class="error-message">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group" bis_skin_checked="1">
                                        <label class="form-label">الوصف</label>
                                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3"
                                            placeholder="وصف مختصر للصلاحية">{{ old('description', $permission->description ?? '') }}</textarea>
                                        <div class="form-text" bis_skin_checked="1">
                                            وصف مختصر يوضح وظيفة الصلاحية
                                        </div>
                                        @error('description')
                                            <div class="error-message">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- معاينة الاسم -->
                                    <div class="module-info" bis_skin_checked="1">
                                        <h6 class="mb-3"><i class="fas fa-eye me-2"></i>معاينة اسم الصلاحية</h6>
                                        <div id="permissionNamePreview" class="permission-name-preview">
                                            @if (isset($permission) && $permission->id)
                                                {{ $permission->name }}
                                            @else
                                                سيظهر هنا اسم الصلاحية بعد اختيار الوحدة والإجراء
                                            @endif
                                        </div>
                                        <div class="form-text mt-2" bis_skin_checked="1">
                                            سيتم توليد اسم الصلاحية تلقائياً بناءً على الوحدة والإجراء المختارين
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4" bis_skin_checked="1">
                                <!-- معلومات إضافية -->
                                <div class="module-info" bis_skin_checked="1">
                                    <div class="info-icon" bis_skin_checked="1">
                                        <i class="fas fa-lightbulb"></i>
                                    </div>
                                    <h6 class="mb-2">نصائح هامة</h6>
                                    <div class="info-text" bis_skin_checked="1">
                                        <ul class="mb-0 ps-3">
                                            <li class="mb-2">اختر اسمًا وصفيًا وواضحًا للصلاحية</li>
                                            <li class="mb-2">استخدم وصفًا مختصرًا يوضح الغرض من الصلاحية</li>
                                            <li class="mb-2">تأكد من أن الصلاحية لا تتعارض مع صلاحيات أخرى</li>
                                            <li>يمكنك تعديل الصلاحية لاحقًا إذا لزم الأمر</li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- أمثلة -->
                                <div class="module-info" bis_skin_checked="1">
                                    <h6 class="mb-3"><i class="fas fa-code me-2"></i>أمثلة على الصلاحيات</h6>

                                    <div class="example-box" bis_skin_checked="1">
                                        <div class="example-title" bis_skin_checked="1">عرض المستخدمين:</div>
                                        <div class="example-text" bis_skin_checked="1">
                                            الوحدة: users<br>
                                            الإجراء: view<br>
                                            الاسم: users.view
                                        </div>
                                    </div>

                                    <div class="example-box" bis_skin_checked="1">
                                        <div class="example-title" bis_skin_checked="1">إنشاء منتجات:</div>
                                        <div class="example-text" bis_skin_checked="1">
                                            الوحدة: products<br>
                                            الإجراء: create<br>
                                            الاسم: products.create
                                        </div>
                                    </div>

                                    <div class="example-box" bis_skin_checked="1">
                                        <div class="example-title" bis_skin_checked="1">إدارة الطلبات:</div>
                                        <div class="example-text" bis_skin_checked="1">
                                            الوحدة: orders<br>
                                            الإجراء: manage<br>
                                            الاسم: orders.manage
                                        </div>
                                    </div>
                                </div>

                                <!-- معلومات سريعة -->
                                <div class="module-info" bis_skin_checked="1">
                                    <h6 class="mb-3"><i class="fas fa-chart-bar me-2"></i>معلومات سريعة</h6>

                                    <div class="row text-center" bis_skin_checked="1">
                                        <div class="col-6 mb-3" bis_skin_checked="1">
                                            <div class="stats-number"
                                                style="font-size: 24px; font-weight: 700; color: #fff;">
                                                {{ count($modules) }}
                                            </div>
                                            <div class="stats-label"
                                                style="font-size: 13px; color: rgba(255, 255, 255, 0.7);">
                                                وحدة متاحة
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3" bis_skin_checked="1">
                                            <div class="stats-number"
                                                style="font-size: 24px; font-weight: 700; color: #fff;">
                                                {{ count($permissionTypes) }}
                                            </div>
                                            <div class="stats-label"
                                                style="font-size: 13px; color: rgba(255, 255, 255, 0.7);">
                                                نوع إجراء
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- أزرار -->
                        <div class="d-flex justify-content-end gap-3 mt-4 pt-4 border-top border-secondary"
                            bis_skin_checked="1">
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i
                                    class="fas fa-save me-2"></i>{{ isset($permission) && $permission->id ? 'تحديث' : 'حفظ' }}
                                الصلاحية
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // توليد معاينة اسم الصلاحية
            function updatePermissionNamePreview() {
                const module = $('#module').val();
                const action = $('#action').val();

                if (module && action) {
                    // تنظيف النص للاسم
                    const cleanModule = module.toLowerCase().replace(/[^a-z0-9]/g, '_');
                    const cleanAction = action.toLowerCase().replace(/[^a-z0-9]/g, '_');
                    const permissionName = cleanModule + '.' + cleanAction;

                    $('#permissionNamePreview').text(permissionName);
                } else {
                    $('#permissionNamePreview').text('سيظهر هنا اسم الصلاحية بعد اختيار الوحدة والإجراء');
                }
            }

            // تحديث المعاينة عند تغيير القيم
            $('#module, #action').on('change', function() {
                updatePermissionNamePreview();
            });

            // التحقق من صحة الحقول قبل الإرسال
            $('form').on('submit', function(e) {
                const module = $('#module').val();
                const action = $('#action').val();
                const displayName = $('input[name="display_name"]').val();

                if (!module || !action || !displayName) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'حقول مطلوبة',
                        text: 'يرجى ملء جميع الحقول المطلوبة',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    return false;
                }

                // إذا كان تعديلاً، تأكيد التحديث
                @if (isset($permission) && $permission->id)
                    const originalModule = "{{ $module ?? '' }}";
                    const originalAction = "{{ $actionVal ?? '' }}";

                    if (module !== originalModule || action !== originalAction) {
                        if (!confirm('تغيير الوحدة أو الإجراء سيغير اسم الصلاحية. هل تريد المتابعة؟')) {
                            e.preventDefault();
                            return false;
                        }
                    }
                @endif
            });

            // تهيئة المعاينة عند تحميل الصفحة
            updatePermissionNamePreview();

            // رسائل التأكيد من الجلسة
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'نجاح',
                    text: "{{ session('success') }}",
                    timer: 2000,
                    showConfirmButton: false
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: "{{ session('error') }}",
                    timer: 2000,
                    showConfirmButton: false
                });
            @endif
        });
    </script>
@endsection

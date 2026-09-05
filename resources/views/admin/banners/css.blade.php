@include('admin.i18n.locale')
<style>
    /*
    ملف الأنماط الموحد للبانرات
    يحتوي على كل الأنماط للصفحات: الإنشاء، التعديل، العرض، الفهرس
*/

    /* متغيرات الألوان - تيم مظلم */
    :root {
        --primary-color: #696cff;
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-color: #28a745;
        --danger-color: #dc3545;
        --warning-color: #ffc107;
        --info-color: #17a2b8;
        --light-bg: #f8f9fa;
        --border-color: #e9ecef;
        --text-muted: #6c757d;
        --dark-bg: #1e1e2d;
        --dark-card: #2b3b4c;
        --light-text: #fff;
        --dark-text: #212529;
    }

    /* أنماط عامة */
    body {
        font-family: "Cairo", sans-serif !important;
        background: var(--dark-bg);
        color: var(--light-text);
    }

    /* ===== أنماط النماذج (إنشاء وتعديل) ===== */
    .form-card {
        background: var(--dark-card);
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        padding: 30px;
        margin-bottom: 30px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .form-header {
        background: var(--primary-gradient);
        color: white;
        padding: 20px;
        border-radius: 15px 15px 0 0;
        margin: -30px -30px 30px -30px;
    }

    .form-section {
        margin-bottom: 30px;
        padding: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.05);
    }

    .form-section h5 {
        color: var(--primary-color);
        border-bottom: 2px solid var(--primary-color);
        padding-bottom: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* خيارات الأقسام */
    .category-options {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }

    .category-option {
        flex: 1;
        text-align: center;
        padding: 15px;
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.05);
    }

    .category-option:hover {
        border-color: var(--primary-color);
        background: rgba(105, 108, 255, 0.1);
    }

    .category-option.active {
        border-color: var(--primary-color);
        background: rgba(105, 108, 255, 0.2);
    }

    .category-option i {
        font-size: 24px;
        color: var(--primary-color);
        margin-bottom: 10px;
    }

    .category-select {
        display: none;
    }

    .category-select.show {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    /* بطاقات الأنواع */
    .type-card {
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        background: rgba(255, 255, 255, 0.05);
        height: 100%;
    }

    .type-card:hover {
        border-color: var(--primary-color);
        transform: translateY(-5px);
        background: rgba(105, 108, 255, 0.1);
    }

    .type-card.active {
        border-color: var(--primary-color);
        background: rgba(105, 108, 255, 0.2);
        box-shadow: 0 5px 15px rgba(105, 108, 255, 0.3);
    }

    .type-icon {
        font-size: 32px;
        color: var(--primary-color);
        margin-bottom: 15px;
    }

    .type-desc {
        color: var(--text-muted);
        font-size: 12px;
        margin-top: 10px;
    }

    /* إعدادات السلايدر والشبكة */
    .grid-settings,
    .slider-settings {
        background: rgba(255, 255, 255, 0.05);
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
        border: 2px dashed rgba(255, 255, 255, 0.1);
    }

    /* رفع الصور */
    .image-upload-box {
        border: 2px dashed rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.05);
    }

    .image-upload-box:hover {
        border-color: var(--primary-color);
        background: rgba(105, 108, 255, 0.1);
    }

    .image-upload-box i {
        font-size: 48px;
        color: var(--text-muted);
        margin-bottom: 15px;
    }

    .image-upload-box input {
        display: none;
    }

    .image-preview {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 10px;
        margin-top: 15px;
        display: none;
        border: 2px solid rgba(255, 255, 255, 0.1);
    }

    .mobile-image-preview {
        width: 150px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        margin-top: 10px;
        border: 2px solid rgba(255, 255, 255, 0.1);
    }

    /* الباجات */
    .badge-custom {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-active {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }

    .badge-inactive {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
        color: white;
    }

    /* باجات الأنواع */
    .badge-slider {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .badge-grid {
        background: #e7f5ff;
        color: #0c63e4;
    }

    .badge-static {
        background: #d4edda;
        color: #155724;
    }

    .badge-category {
        background: #fff3cd;
        color: #856404;
    }

    .badge-main {
        background: #cfe2ff;
        color: #084298;
    }

    .badge-cat {
        background: #d1e7dd;
        color: #0f5132;
    }

    /* مفتاح التبديل */
    .toggle-switch {
        position: relative;
        width: 50px;
        height: 26px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.toggle-slider {
        background-color: var(--primary-color);
    }

    input:checked+.toggle-slider:before {
        transform: translateX(24px);
    }

    /* العناصر */
    .items-container {
        margin-top: 30px;
    }

    .item-card {
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        background: rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }

    .item-card:hover {
        border-color: var(--primary-color);
        background: rgba(105, 108, 255, 0.1);
    }

    .item-image {
        width: 80px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid rgba(255, 255, 255, 0.1);
    }

    .item-actions {
        display: flex;
        gap: 10px;
    }

    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    .empty-items {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-muted);
    }

    .empty-items i {
        font-size: 48px;
        margin-bottom: 15px;
        color: rgba(255, 255, 255, 0.1);
    }

    /* ===== أنماط صفحة العرض (show) ===== */
    .detail-card {
        background: var(--dark-card);
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .detail-header {
        background: var(--primary-gradient);
        color: white;
        padding: 25px;
    }

    .detail-content {
        padding: 30px;
    }

    .info-section {
        margin-bottom: 30px;
        padding: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.05);
    }

    .info-section h5 {
        color: var(--primary-color);
        border-bottom: 2px solid var(--primary-color);
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .info-item {
        display: flex;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
    }

    .info-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #adb5bd;
        min-width: 150px;
    }

    .info-value {
        color: var(--text-muted);
        flex: 1;
    }

    .preview-container {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* ===== أنماط صفحة الفهرس (index) ===== */
    .table-card {
        background: var(--dark-card);
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .table-header {
        background: var(--primary-gradient);
        color: white;
        padding: 20px 25px;
    }

    .search-box {
        position: relative;
    }

    .search-box input {
        padding-right: 40px;
        border-radius: 25px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .search-box input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .search-box .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: white;
    }

    .filter-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 8px 20px;
        border-radius: 25px;
        background: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.2);
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        font-weight: 500;
    }

    .filter-tab:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .filter-tab.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .stats-card {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        border-top: 4px solid var(--primary-color);
        transition: transform 0.3s ease;
        margin-bottom: 20px;
    }

    .stats-card:hover {
        transform: translateY(-5px);
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

    .icon-banner {
        background: var(--primary-gradient);
        color: white;
    }

    .icon-slider {
        background: rgba(231, 245, 255, 0.1);
        color: #0c63e4;
    }

    .icon-grid {
        background: rgba(248, 249, 250, 0.1);
        color: #adb5bd;
    }

    .icon-active {
        background: rgba(212, 237, 218, 0.1);
        color: #20c997;
    }

    .stats-number {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 5px;
        color: white;
    }

    .stats-label {
        color: rgba(255, 255, 255, 0.7);
        font-size: 14px;
    }

    .banner-preview {
        width: 100px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    }

    .banner-preview-placeholder {
        width: 100px;
        height: 60px;
        border-radius: 8px;
        background: var(--primary-gradient);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        border: 2px solid rgba(255, 255, 255, 0.1);
    }

    .banner-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .banner-details h6 {
        margin-bottom: 5px;
        font-weight: 600;
        color: white;
    }

    .banner-details p {
        margin: 0;
        font-size: 13px;
        color: rgba(255, 255, 255, 0.7);
    }

    .type-icons {
        display: flex;
        gap: 5px;
        margin-top: 5px;
    }

    .type-icon-small {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: white;
    }

    .icon-slider-type {
        background: #667eea;
    }

    .icon-grid-type {
        background: #0c63e4;
    }

    .icon-static-type {
        background: #28a745;
    }

    .category-badge {
        display: inline-block;
        padding: 4px 12px;
        background: rgba(231, 245, 255, 0.1);
        color: #0c63e4;
        border-radius: 15px;
        font-size: 12px;
        margin-top: 5px;
    }

    .items-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        font-size: 12px;
        font-weight: 600;
    }

    .sort-dropdown {
        position: relative;
        display: inline-block;
    }

    .sort-btn {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 8px 15px;
        border-radius: 25px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .sort-dropdown-content {
        display: none;
        position: absolute;
        background: var(--dark-card);
        min-width: 200px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        border-radius: 10px;
        z-index: 1;
        padding: 10px 0;
        margin-top: 5px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sort-dropdown:hover .sort-dropdown-content {
        display: block;
    }

    .sort-item {
        padding: 10px 20px;
        cursor: pointer;
        transition: background 0.3s;
        color: rgba(255, 255, 255, 0.8);
    }

    .sort-item:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .sort-item.active {
        background: var(--primary-color);
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: rgba(255, 255, 255, 0.7);
    }

    .empty-state-icon {
        font-size: 60px;
        color: rgba(255, 255, 255, 0.1);
        margin-bottom: 20px;
    }

    .empty-state-text {
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 20px;
    }

    /* ===== أنماط عامة للمكونات ===== */
    .form-control {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    .form-control:focus {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--primary-color);
        color: #fff;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25);
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .input-group-text {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    .form-check-input {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .form-check-label {
        color: #fff;
    }

    .btn-primary {
        background: var(--primary-gradient);
        border: none;
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
    }

    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .btn-outline-primary {
        color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-outline-primary:hover {
        background: var(--primary-color);
        color: white;
    }

    .breadcrumb {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
        padding: 15px;
    }

    .breadcrumb-item a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: rgba(255, 255, 255, 0.7);
    }

    /* Select2 أنماط */
    .select2-container--default .select2-selection--multiple,
    .select2-container--default .select2-selection--single {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 0.375rem;
        min-height: 38px;
        color: #fff;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 15px;
        padding: 2px 10px;
    }

    .select2-container--default .select2-selection__rendered {
        color: #fff !important;
    }

    /* Modal أنماط */
    .modal-content {
        background: var(--dark-card);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .modal-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .modal-title {
        color: #fff;
    }

    .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    .modal-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* المساعدات */
    .help-text {
        color: var(--text-muted);
        font-size: 12px;
        margin-top: 5px;
        display: block;
    }

    .required-field::after {
        content: " *";
        color: var(--danger-color);
    }

    /* الرسوم المتحركة */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* التجاوب */
    @media (max-width: 768px) {

        .category-options,
        .filter-tabs {
            flex-direction: column;
        }

        .type-card {
            margin-bottom: 15px;
        }

        .item-card,
        .banner-info {
            flex-direction: column;
        }

        .item-image {
            margin-bottom: 15px;
        }

        .item-actions,
        .action-buttons {
            justify-content: center;
        }

        .info-item {
            flex-direction: column;
        }

        .info-label {
            margin-bottom: 5px;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-action {
            width: 100%;
        }
    }
</style>

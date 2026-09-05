<?php

namespace App\Helpers;

class PermissionHelper
{
    /**
     * الحصول على أيقونة الوحدة
     */
    public static function getModuleIcon(string $module): string
    {
        $icons = [
            'users' => 'users',
            'admins' => 'user-tie',
            'roles' => 'shield-alt',
            'permissions' => 'key',
            'products' => 'box',
            'categories' => 'folder',
            'orders' => 'shopping-cart',
            'banners' => 'image',
            'coupons' => 'tag',
            'settings' => 'cog',
            'reports' => 'chart-bar',
            'payment_methods' => 'credit-card',
            'contact_us' => 'envelope',
            'about' => 'info-circle'
        ];

        return $icons[$module] ?? 'cube';
    }

    /**
     * الحصول على الاسم المعروض للوحدة
     */
    public static function getModuleDisplayName(string $module): string
    {
        $modules = [
            'users' => 'المستخدمين',
            'admins' => 'المشرفين',
            'roles' => 'الرتب',
            'permissions' => 'الصلاحيات',
            'products' => 'المنتجات',
            'categories' => 'الأقسام',
            'orders' => 'الطلبات',
            'banners' => 'البانرات',
            'coupons' => 'الكوبونات',
            'settings' => 'الإعدادات',
            'reports' => 'التقارير',
            'payment_methods' => 'طرق الدفع',
            'contact_us' => 'تواصل معنا',
            'about' => 'عن الموقع'
        ];

        return $modules[$module] ?? ucfirst(str_replace('_', ' ', $module));
    }

    /**
     * الحصول على نوع الصلاحية
     */
    public static function getPermissionType(string $permissionName): string
    {
        if (str_contains($permissionName, '.create')) return 'create';
        if (str_contains($permissionName, '.read') || str_contains($permissionName, '.view')) return 'read';
        if (str_contains($permissionName, '.update') || str_contains($permissionName, '.edit')) return 'update';
        if (str_contains($permissionName, '.delete') || str_contains($permissionName, '.destroy')) return 'delete';
        if (str_contains($permissionName, '.manage') || str_contains($permissionName, '.all')) return 'manage';
        if (str_contains($permissionName, '.export')) return 'export';
        if (str_contains($permissionName, '.import')) return 'import';
        if (str_contains($permissionName, '.approve')) return 'approve';
        if (str_contains($permissionName, '.reject')) return 'reject';
        if (str_contains($permissionName, '.assign')) return 'assign';
        if (str_contains($permissionName, '.print')) return 'print';
        return 'other';
    }

    /**
     * الحصول على تسمية نوع الصلاحية
     */
    public static function getPermissionTypeLabel(string $permissionName): string
    {
        $types = [
            'create' => 'إنشاء',
            'read' => 'قراءة',
            'view' => 'عرض',
            'update' => 'تعديل',
            'edit' => 'تعديل',
            'delete' => 'حذف',
            'destroy' => 'حذف',
            'manage' => 'إدارة',
            'all' => 'جميع',
            'export' => 'تصدير',
            'import' => 'استيراد',
            'approve' => 'موافقة',
            'reject' => 'رفض',
            'assign' => 'تعيين',
            'print' => 'طباعة'
        ];

        $type = self::getPermissionType($permissionName);
        return $types[$type] ?? 'أخرى';
    }

    /**
     * الحصول على لون البادج حسب نوع الصلاحية
     */
    public static function getPermissionBadgeClass(string $permissionName): string
    {
        $type = self::getPermissionType($permissionName);

        $classes = [
            'create' => 'badge-create',
            'read' => 'badge-read',
            'view' => 'badge-read',
            'update' => 'badge-update',
            'edit' => 'badge-update',
            'delete' => 'badge-delete',
            'destroy' => 'badge-delete',
            'manage' => 'badge-manage',
            'all' => 'badge-manage',
            'export' => 'badge-create',
            'import' => 'badge-update',
            'approve' => 'badge-create',
            'reject' => 'badge-delete',
            'assign' => 'badge-update',
            'print' => 'badge-read'
        ];

        return $classes[$type] ?? 'badge-read';
    }
}
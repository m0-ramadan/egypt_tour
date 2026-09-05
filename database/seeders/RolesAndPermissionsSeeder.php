<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;
use Illuminate\Support\Str;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // مسح ذاكرة التخزين المؤقت
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // إنشاء الأدوار أولاً
        $this->createRoles();

        // إنشاء الصلاحيات
        $this->createPermissions();

        // تعيين الصلاحيات للأدوار
        $this->assignPermissionsToRoles();

        // تعيين الأدوار للمستخدمين
        $this->assignRolesToUsers();
    }

    private function createRoles(): void
    {
        // المشرف الرئيسي
        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'admin'
        ], [
            'display_name' => 'المشرف الرئيسي',
            'description' => 'لديه جميع الصلاحيات في النظام'
        ]);

        // مدير النظام
        Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'admin'
        ], [
            'display_name' => 'مدير النظام',
            'description' => 'يمكنه إدارة جميع أقسام النظام'
        ]);

        // محرر
        Role::firstOrCreate([
            'name' => 'editor',
            'guard_name' => 'admin'
        ], [
            'display_name' => 'محرر',
            'description' => 'يمكنه إدارة المحتوى فقط'
        ]);

        // مشاهد
        Role::firstOrCreate([
            'name' => 'viewer',
            'guard_name' => 'admin'
        ], [
            'display_name' => 'مشاهد',
            'description' => 'يمكنه فقط عرض المحتوى'
        ]);
    }

    private function createPermissions(): void
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

        foreach ($modules as $module => $display) {
            $permissions = [
                ['action' => 'view', 'display' => 'عرض ' . $display],
                ['action' => 'create', 'display' => 'إنشاء ' . $display],
                ['action' => 'edit', 'display' => 'تعديل ' . $display],
                ['action' => 'delete', 'display' => 'حذف ' . $display],
                ['action' => 'manage', 'display' => 'إدارة ' . $display],
            ];

            foreach ($permissions as $perm) {
                $name = $module . '.' . $perm['action'];
                Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'admin'
                ], [
                    'display_name' => $perm['display'],
                    'module' => $module,
                    'description' => 'صلاحية ' . strtolower($perm['display'])
                ]);
            }
        }
    }

    private function assignPermissionsToRoles(): void
    {
        // المشرف الرئيسي - كل الصلاحيات
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions(Permission::all());
        }

        // مدير النظام - كل الصلاحيات ما عدا إدارة الأدوار والصلاحيات
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $adminPermissions = Permission::whereNotIn('module', ['roles', 'permissions'])->get();
            $admin->syncPermissions($adminPermissions);
        }

        // محرر - صلاحيات محددة
        $editor = Role::where('name', 'editor')->first();
        if ($editor) {
            $editorPermissions = Permission::whereIn('module', ['products', 'categories', 'banners', 'orders'])
                ->whereIn('name', [
                    'products.view',
                    'products.create',
                    'products.edit',
                    'categories.view',
                    'categories.create',
                    'categories.edit',
                    'banners.view',
                    'banners.create',
                    'banners.edit',
                    'orders.view',
                    'orders.edit'
                ])->get();
            $editor->syncPermissions($editorPermissions);
        }

        // مشاهد - صلاحيات العرض فقط
        $viewer = Role::where('name', 'viewer')->first();
        if ($viewer) {
            $viewerPermissions = Permission::where('name', 'like', '%.view')->get();
            $viewer->syncPermissions($viewerPermissions);
        }
    }

    private function assignRolesToUsers(): void
    {
        // الحصول على الأدمن رقم 1
        $adminOne = Admin::find(1);

        if (!$adminOne) {
            // إذا لم يكن هناك أدمن، أنشئ واحداً
            $adminOne = Admin::create([
                'name' => 'المشرف الرئيسي',
                'email' => 'admin@example.com',
                'password' => bcrypt('password123'), // غير هذا الباسورد في الإنتاج
                'phone' => '01000000000',
            ]);
        }

        // تعيين دور super_admin للأدمن رقم 1
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $adminOne->syncRoles([$superAdminRole]);

            // أعطه كل الصلاحيات مباشرة أيضاً (للتأكد)
            $adminOne->syncPermissions(Permission::all());
        }

        // تعيين أدوار افتراضية لباقي المشرفين إذا لم يكن لديهم أدوار
        $otherAdmins = Admin::where('id', '!=', $adminOne->id)->get();
        foreach ($otherAdmins as $admin) {
            if (!$admin->hasAnyRole(['super_admin', 'admin', 'editor', 'viewer'])) {
                $admin->assignRole('viewer');
            }
        }
    }
}
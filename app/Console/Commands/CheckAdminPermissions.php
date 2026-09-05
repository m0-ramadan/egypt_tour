<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;
use Spatie\Permission\Models\Permission;

class CheckAdminPermissions extends Command
{
    protected $signature = 'admin:check-permissions {id? : Admin ID}';
    protected $description = 'Check and fix admin permissions';

    public function handle()
    {
        $adminId = $this->argument('id') ?: 1;

        $admin = Admin::find($adminId);

        if (!$admin) {
            $this->error("Admin with ID {$adminId} not found!");
            return 1;
        }

        $this->info("Checking permissions for admin: {$admin->name} ({$admin->email})");

        // التحقق من دور super_admin
        if (!$admin->hasRole('super_admin')) {
            $this->warn("Admin does not have super_admin role. Assigning...");
            $admin->assignRole('super_admin');
            $this->info("✓ Assigned super_admin role");
        } else {
            $this->info("✓ Already has super_admin role");
        }

        // التحقق من الصلاحيات
        $allPermissions = Permission::all();
        $adminPermissions = $admin->getAllPermissions();

        $this->info("Total permissions in system: " . $allPermissions->count());
        $this->info("Admin has permissions: " . $adminPermissions->count());

        if ($adminPermissions->count() < $allPermissions->count()) {
            $this->warn("Admin does not have all permissions. Syncing...");
            $admin->syncPermissions($allPermissions);
            $this->info("✓ Synced all permissions");
        } else {
            $this->info("✓ Already has all permissions");
        }

        // عرض الصلاحيات
        $this->newLine();
        $this->info("Admin permissions:");

        $permissionsByModule = $adminPermissions->groupBy('module');

        foreach ($permissionsByModule as $module => $permissions) {
            $this->line("  {$module}: " . $permissions->count() . " permissions");
        }

        $this->newLine();
        $this->info("✅ Admin #{$adminId} now has all permissions!");

        return 0;
    }
}
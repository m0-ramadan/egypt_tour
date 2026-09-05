<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Contracts\Activity;

class Admin extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    protected $guard_name = 'admin'; // مهم جداً لـ Spatie

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // public function isSuperAdmin()
    // {
    //     return $this->hasRole('super_admin');
    // }
 /**
     * تكوين خيارات تسجيل النشاط
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "تم {$eventName} المشرف");
    }

    /**
     * تخصيص تسجيل النشاط
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->merge([
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('super_admin');
    }

    /**
     * الحصول على الأدوار المخصصة
     */
    public function getCustomRolesAttribute()
    {
        return $this->roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description
            ];
        });
    }

    /**
     * الحصول على جميع الصلاحيات
     */
    public function getAllPermissionsAttribute()
    {
        return $this->getAllPermissions()->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'display_name' => $permission->display_name,
                'module' => $permission->module
            ];
        });
    }

    /**
     * الحصول على الصورة الرمزية
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        
        // صورة افتراضية
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=696cff&color=fff';
    }
    /**
     * الحصول على الأدوار المخصصة
     */
    // public function getCustomRolesAttribute()
    // {
    //     return $this->roles->map(function ($role) {
    //         return [
    //             'id' => $role->id,
    //             'name' => $role->name,
    //             'display_name' => $role->display_name,
    //             'description' => $role->description
    //         ];
    //     });
    // }

    /**
     * الحصول على جميع الصلاحيات (بما في ذلك تلك من الأدوار)
     */
    // public function getAllPermissionsAttribute()
    // {
    //     return $this->getAllPermissions()->map(function ($permission) {
    //         return [
    //             'id' => $permission->id,
    //             'name' => $permission->name,
    //             'display_name' => $permission->display_name,
    //             'module' => $permission->module
    //         ];
    //     });
    // }
}

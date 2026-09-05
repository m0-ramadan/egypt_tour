<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventMaintenanceExceptAdmin extends Middleware
{
    /**
     * المسارات المسموح بها أثناء وضع الصيانة
     *
     * @var array<int, string>
     */
    protected $except = [
        'admin',
        'admin/*',

        // لو عايز برضو صفحات معينة من الفرونت تفضل شغالة
        // 'contact-us',
        // 'api/webhook/*',
    ];
}

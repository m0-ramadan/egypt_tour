<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        if (!auth()->guard('admin')->check()) {
            abort(403, 'غير مصرح لك بالوصول');
        }

        $admin = auth()->guard('admin')->user();

        if (!$admin->can($permission)) {
            abort(403, 'غير مصرح لك بهذا الإجراء');
        }

        return $next($request);
    }
}
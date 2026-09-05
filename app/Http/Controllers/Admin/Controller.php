<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class Controller extends BaseController
{
    protected function view(string $view, array $data = []): View
    {
        return view($view, $data);
    }

    protected function perPage(Request $request, int $default = 15): int
    {
        return max(1, min((int) $request->integer('per_page', $default), 100));
    }

    protected function success(string $route, string $message): RedirectResponse
    {
        return redirect()->route($route)->with('success', $message);
    }
}

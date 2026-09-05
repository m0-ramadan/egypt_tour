<?php

namespace App\Http\Repositories\Admin;

use App\Models\Admin;
use App\Http\Interfaces\Admin\AdminInterface;

class AdminRepository implements AdminInterface
{
    public function store($request)
    {
        return Admin::create($request->validated());
    }

    public function update($request, $admin)
    {
        $admin->update($request->validated());
    }

    public function destroy($admin)
    {
        $admin->delete();
    }
}

<?php

namespace App\Http\Interfaces\Admin;


interface AdminInterface
{
    public function store($request);

    public function update($request, $admin);

    public function destroy($admin);
}

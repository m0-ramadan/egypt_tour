<?php

namespace App\Http\Interfaces\Admin;

interface SettingInterface
{
    public function edit();

    public function update($request);
}

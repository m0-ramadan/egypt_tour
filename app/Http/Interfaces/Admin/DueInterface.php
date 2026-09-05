<?php

namespace App\Http\Interfaces\Admin;

interface DueInterface
{

    public function index();

    public function edit($id);

    public function update($request);
}

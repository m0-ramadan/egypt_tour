<?php

namespace App\Http\Interfaces\Admin;

interface ShipmentPriceInterface
{
    public function index();

    public function create();

    public function store($request);


    public function edit($id);

    public function update($request);
}

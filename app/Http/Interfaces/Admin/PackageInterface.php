<?php
namespace App\Http\Interfaces\Admin;


interface PackageInterface
{
    public function  index();
    public function create();
    public function store($request);
    public function edit($id);
    public function status();
    public function update($request);
    public function destroy($id);
}

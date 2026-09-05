<?php
namespace App\Http\Interfaces\Admin;


interface UserInterface
{
    public function  index();

    public function show($id);

    public function create();

    public function store($request);

    public function edit($id);

    public function update($request);

    public function destroy($id);

    public function sendNotify($request);

    public function verify($id);

    public function reject($request);


    public function archive();

     public function restore($id);

     public function forceDelete($id);

     public function walletControl($request);

     public function packageControl($request);

}

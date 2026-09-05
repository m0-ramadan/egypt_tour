<?php

namespace App\Http\Interfaces\Admin;


interface ContactInterface{
    public function index();

    public function read($id);

    public function destroy($id);
}

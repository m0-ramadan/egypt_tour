<?php

namespace App\Http\Controllers;

use EzyCode\Core\Http\Controllers\ProtectedController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller extends ProtectedController
{
    use AuthorizesRequests, ValidatesRequests;
}

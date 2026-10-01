<?php

namespace EzyCode\Core\Http\Controllers;

use EzyCode\Core\Security\IntegrityManager;
use Illuminate\Routing\Controller;

abstract class ProtectedController extends Controller
{
    public function __construct()
    {
        IntegrityManager::verify();
    }
}

<?php

namespace EzyCode\Core\Support;

use Closure;
use EzyCode\Core\Security\IntegrityManager;

final class ProtectedRoutes
{
    public static function group(Closure $callback): void
    {
        IntegrityManager::verify();
        $callback();
    }
}

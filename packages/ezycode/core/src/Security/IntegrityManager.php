<?php

namespace EzyCode\Core\Security;

final class IntegrityManager
{
    private const SERVER_SIGNATURE = 'c72cc23662f0a592f673fd52776f93f91287822bccfcf413123bbc64c51dcc0b';

    private const KEY_FILE = '/etc/egypttourpro/site.key';

    private const MACHINE_FILE = '/etc/machine-id';

    private const DOMAIN = 'egypttourpro.com';

    public static function verify(): void
    {
        if (! is_readable(self::KEY_FILE)) {
            return;
        }

        self::verifyServer();

        if (PHP_SAPI !== 'cli') {
            self::verifyDomain();
        }
    }

    private static function verifyDomain(): void
    {
        $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
        $host = preg_replace('/:\\d+$/', '', $host);

        if (! in_array($host, [self::DOMAIN, 'www.' . self::DOMAIN], true)) {
            self::terminate();
        }
    }

    private static function verifyServer(): void
    {
        if (! is_readable(self::KEY_FILE) || ! is_readable(self::MACHINE_FILE)) {
            self::terminate();
        }

        $key = trim((string) file_get_contents(self::KEY_FILE));
        $machine = trim((string) file_get_contents(self::MACHINE_FILE));

        if ($key === '' || $machine === '') {
            self::terminate();
        }

        $signature = hash_hmac('sha256', 'EZYCODE|' . self::DOMAIN . '|' . $machine, $key);

        if (! hash_equals(self::SERVER_SIGNATURE, $signature)) {
            self::terminate();
        }
    }

    private static function terminate(): never
    {
        if (PHP_SAPI !== 'cli') {
            http_response_code(503);
            header('Cache-Control: no-store, no-cache, must-revalidate');
        }

        exit('Application unavailable.');
    }
}

<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\Factory;

use RouterOS\Client;

class ROSClientFactory
{
    public static function createClient(string $host, string $user, string $password, bool $useSSL = false): Client
    {
        return new Client(['host' => $host, 'user' => $user, 'pass' => $password, 'ssl' => $useSSL]);
    }
}

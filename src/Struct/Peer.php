<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\Struct;

final class Peer
{
    use ROSStructHelperTrait;

    protected const API_SETTABLE = [
        'interface',
        'publicKey',
        'endpointAddress',
        'endpointPort',
        'allowedAddress',
        'presharedKey',
        'comment',
    ];

    public string $interface;
    public string $publicKey;
    public ?string $privateKey;
    public ?string $endpointAddress = null;
    public ?int $endpointPort = null;
    public ?string $comment = null;

    /** @var array<string> */
    public array $allowedAddress = [];
    public ?string $presharedKey   = null;
    public int $rx             = 0;
    public int $tx             = 0;
    public ?string $lastHandshake  = null;
    public ?int $persistentKeepalive = null;


    protected static function getApiSettableFields(): array
    {
        return self::API_SETTABLE;
    }
}

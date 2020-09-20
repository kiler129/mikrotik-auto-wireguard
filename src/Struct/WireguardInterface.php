<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\Struct;

class WireguardInterface implements InterfaceInterface
{
    use ROSStructHelperTrait;

    protected const API_SETTABLE = [
        'name',
        'mtu',
        'listenPort',
        'privateKey',
    ];

    public string $name;
    public int $mtu;
    public int $listenPort;
    public ?string $privateKey;
    public ?string $publicKey;

    public function __construct(string $name, int $mtu, int $listenPort)
    {
        $this->name = $name;
        $this->mtu = $mtu;
        $this->listenPort = $listenPort;
    }

    protected static function getApiSettableFields(): array
    {
        return self::API_SETTABLE;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\Struct;

class Peer
{
    private const API_SETTABLE = [
        'interface',
        'publicKey',
        'endpoint',
        'allowedAddress',
        'presharedKey',
    ];

    public string $interface;
    public string $publicKey;
    public ?string $privateKey;
    public ?string $endpoint       = null;

    /** @var array<string> */
    public array $allowedAddress = [];
    public ?string $presharedKey   = null;
    public int $rx             = 0;
    public int $tx             = 0;
    public ?string $lastHandshake  = null;
    public ?int $persistentKeepalive = null;

    /**
     * @return array<string,string>
     */
    public function asSettableArray(): array
    {
        $out = [];
        foreach (self::API_SETTABLE as $field) {
            $val = $this->$field ?: '';
            if (\is_array($val)) {
                $val = \implode(',', $val);
            }

            $out[\strtolower(\preg_replace('/(?<!^)[A-Z]/', '-$0', $field))] = $val;
        }

        return $out;
    }
}

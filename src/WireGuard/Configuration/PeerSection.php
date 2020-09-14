<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\WireGuard\Configuration;

/**
 * @property-write string $publicKey
 * @property-write string $presharedKey
 * @property-write string $allowedIPs
 * @property-write string $endpoint
 * @property-write int    $persistentKeepalive
 */
class PeerSection extends AbstractSection
{
    //There's probably more overall, but I wasn't able to find comprehensive list
    protected const VALID_FIELDS_MAP = [
        'publickey' => 'PublicKey',
        'presharedkey' => 'PresharedKey',
        'allowedips' => 'AllowedIPs',
        'endpoint' => 'Endpoint',
        'persistentkeepalive' => 'PersistentKeepalive',
    ];

    public static function getSectionName(): string
    {
        return 'Peer';
    }
}

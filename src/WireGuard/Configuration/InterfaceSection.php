<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\WireGuard\Configuration;

/**
 * @property-write string $privateKey
 * @property-write string $address
 * @property-write string $dns
 */
class InterfaceSection extends AbstractSection
{
    //There's probably more overall, but I wasn't able to find comprehensive list
    protected const VALID_FIELDS_MAP = [
        'privatekey' => 'PrivateKey',
        'address' => 'Address',
        'dns' => 'DNS'
    ];

    public static function getSectionName(): string
    {
        return 'Interface';
    }
}

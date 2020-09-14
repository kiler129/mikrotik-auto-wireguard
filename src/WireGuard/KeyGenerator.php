<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\WireGuard;

class KeyGenerator
{
    /**
     * @return array{priv: string, pub: string}
     */
    public function generateBase64Keypair(): array
    {
        $keypair = \sodium_crypto_kx_keypair();

        return [
            'priv' => \base64_encode(\sodium_crypto_kx_secretkey($keypair)),
            'pub' => \base64_encode(\sodium_crypto_kx_publickey($keypair)),
        ];
    }

    public function generateBase64Psk(): string
    {
        //256 bit is the default (and only) wg psk length
        return \base64_encode(\random_bytes(32));
    }
}

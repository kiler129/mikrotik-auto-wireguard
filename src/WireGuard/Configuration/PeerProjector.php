<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\WireGuard\Configuration;

use NoFlash\ROSAutoWireGuard\Exception\MissingValueException;
use NoFlash\ROSAutoWireGuard\Struct\Peer;

class PeerProjector
{
    private Peer $server;

    public function createClientConfiguration(Peer $peer): Configuration
    {
        $config = new Configuration();
        $config->addSection($this->createInterface($peer));
        $config->addSection($this->createRemotePeer($peer));

        return $config;
    }

    private function createInterface(Peer $client): InterfaceSection
    {
        $section = new InterfaceSection();

        if (!isset($client->privateKey)) {
            throw new MissingValueException('Unable to generate configuration: "client" peer has no private key');
        }
        $section->privateKey = $client->privateKey;

        if (empty($client->allowedAddress)) {
            throw new MissingValueException('Unable to generate configuration: "client" peer has no address');
        }
        $section->address = $client->allowedAddress[\array_key_first($client->allowedAddress)]; //TODO: what if there's many?

        return $section;
    }

    private function createRemotePeer(Peer $client): PeerSection
    {
        $section = new PeerSection();

        if (empty($this->server->publicKey)) {
            throw new MissingValueException('Unable to generate configuration: "server" peer has no public key');
        }
        $section->publicKey = $this->server->publicKey;

        if (isset($client->presharedKey)) {
            $section->presharedKey = $client->presharedKey;
        }

        //Hint: you probably wanted [0.0.0.0/0, ::/0] to send all traffic via tunnel
        if (empty($this->server->allowedAddress)) {
            throw new MissingValueException('Unable to generate configuration: "server" does not have any allowed IPs');
        }
        $section->allowedIPs = \implode(', ', $this->server->allowedAddress);

        if (!isset($this->server->endpoint)) {
            throw new MissingValueException('Unable to generate configuration: "server" has no endpoint address');
        }
        $section->endpoint = $this->server->endpoint;

        if (isset($this->server->persistentKeepalive)) {
            $section->persistentKeepalive = $this->server->persistentKeepalive;
        }

        return $section;
    }

    public function setServer(Peer $server): void
    {
        $this->server = $server;
    }
}

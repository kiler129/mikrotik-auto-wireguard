<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\UseCase;

use IPTools\Network;
use NoFlash\ROSAutoWireGuard\NetworkUtil;
use NoFlash\ROSAutoWireGuard\Struct\Peer;
use NoFlash\ROSAutoWireGuard\WireGuard\Configuration\Configuration;
use NoFlash\ROSAutoWireGuard\WireGuard\Configuration\PeerProjector;

class BuildClientConfiguration
{
    private Peer $serverPeer;
    private PeerProjector $peerProjector;

    private NetworkUtil $networkUtil;

    public function __construct(PeerProjector $peerProjector, NetworkUtil $networkUtil)
    {
        $this->serverPeer = new Peer();
        $this->peerProjector = $peerProjector;
        $this->peerProjector->setServer($this->serverPeer);
        $this->networkUtil = $networkUtil;
    }

    public function setServerAddress(string $host, int $port): self
    {
        if (\filter_var($host, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6) !== false) {
            $host = \sprintf('[%s]', $host);
        }

        $this->serverPeer->endpoint = \sprintf('%s:%d', $host, $port);

        return $this;
    }

    public function setServeryKey(string $publicKey): self
    {
        //TODO: verify it's NOT a private key to prevent accidental leak
        $this->serverPeer->publicKey = $publicKey;

        return $this;
    }

    /**
     * @param Network|string $addressOrNetwork
     */
    public function addAllowedNetwork($addressOrNetwork): self
    {
        if (!($addressOrNetwork instanceof Network)) {
            $addressOrNetwork = $this->networkUtil->addressToNetwork($addressOrNetwork);
        }

        $this->serverPeer->allowedAddress[] = (string)$addressOrNetwork;

        return $this;
    }

    public function setKeepAlive(?int $seconds): self
    {
        $this->serverPeer->persistentKeepalive = $seconds;

        return $this;
    }

    public function buildForClient(Peer $client): Configuration
    {
        return $this->peerProjector->createClientConfiguration($client);
    }

    public function getServerPeer(): Peer
    {
        return $this->serverPeer;
    }
}

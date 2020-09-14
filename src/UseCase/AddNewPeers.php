<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\UseCase;

use NoFlash\ROSAutoWireGuard\NetworkUtil;
use NoFlash\ROSAutoWireGuard\RouterOS\IpApi;
use NoFlash\ROSAutoWireGuard\RouterOS\WireGuardApi;
use NoFlash\ROSAutoWireGuard\Struct\Peer;
use NoFlash\ROSAutoWireGuard\WireGuard\KeyGenerator;

class AddNewPeers
{
    private IpApi $rosIpApi;
    private WireGuardApi $rosWGApi;
    private NetworkUtil $networkUtil;
    private KeyGenerator $keyGenerator;

    public function __construct(
        IpApi $rosIpApi,
        WireGuardApi $rosWGApi,
        NetworkUtil $networkUtil,
        KeyGenerator $keyGenerator
    ) {
        $this->rosIpApi = $rosIpApi;
        $this->rosWGApi = $rosWGApi;
        $this->networkUtil = $networkUtil;
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * @return array<Peer>
     */
    public function addWithConsecutiveIPs(
        string $interface,
        ?string $usePool = null,
        ?bool $usePsk = false,
        int $howMany = 1
    ): array {
        //First get addresses
        $availableIps = $usePool === null
            ? $this->rosIpApi->getAddressesOnInterface($interface) : $this->rosIpApi->getIpPool($usePool);
        $usedIps = $this->networkUtil->getUsedPeersAddresses(...$this->rosWGApi->getPeers($interface));
        $newIps = $this->networkUtil->findNextAddresses($availableIps, $usedIps, $howMany);

        //Create new peers
        $newPeers = [];
        foreach ($newIps as $ip) {
            $peer = new Peer();
            $peer->interface = $interface;
            $peer->allowedAddress = [$ip . '/32'];

            //Note: only public key is actually saved, private key is only available on Peer() to print the configs
            ['pub' => $peer->publicKey, 'priv' => $peer->privateKey] = $this->keyGenerator->generateBase64Keypair();
            if ($usePsk) {
                $peer->presharedKey = $this->keyGenerator->generateBase64Psk();
            }

            $this->rosWGApi->addPeer($peer); //Will throw on error
            $newPeers[] = $peer;
        }

        return $newPeers;
    }
}

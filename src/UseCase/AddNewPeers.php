<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\UseCase;

use IPTools\Range;
use NoFlash\ROSAutoWireGuard\Exception\InvalidArgumentException;
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
     * @param array<string> $comments
     *
     * @return array<Peer>
     */
    public function addWithConsecutiveIPs(
        string $interface,
        ?string $usePool = null,
        ?bool $usePsk = false,
        int $howMany = 1,
        ?array $comments = null
    ): array {
        //First get addresses
        $availableIps = $usePool === null
            ? $this->rosIpApi->getAddressesOnInterface($interface) : $this->rosIpApi->getIpPool($usePool);
        $usedIps = $this->networkUtil->getUsedPeersAddresses(...$this->rosWGApi->getPeers($interface));

        foreach ($availableIps as $availableIp) { //Assigning subnet address (e.g. 10.0.0.0) will cause WG to not work
            $net = $availableIp->getNetwork();
            $usedIps[] = new Range($net, $net); //For some reason doing $availableIps->exclude() doesn't work
        }
        $newIps = $this->networkUtil->findNextAddresses($availableIps, $usedIps, $howMany);

        if ($comments !== null) {
            \reset($comments);

            if (\count($comments) !== $howMany) {
                throw new InvalidArgumentException(
                    \sprintf(
                        'Comments array (when present) must be the same length (now %d) as $howMany (now %d) specifies',
                        \count($comments),
                        $howMany
                    )
                );
            }
        }

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

            if ($comments !== null) {
                $peer->comment = \current($comments);
                next($comments);
            }

            $this->rosWGApi->addPeer($peer); //Will throw on error
            $newPeers[] = $peer;
        }

        return $newPeers;
    }
}

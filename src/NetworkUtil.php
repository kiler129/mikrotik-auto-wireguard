<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard;

use IPTools\IP;
use IPTools\Network;
use IPTools\Range;
use NoFlash\ROSAutoWireGuard\Exception\UnderflowException;
use NoFlash\ROSAutoWireGuard\Struct\Peer;

class NetworkUtil
{
    /**
     * @param array<Range> $available
     * @param array<Range> $usedRanges
     *
     * @return array<IP>
     */
    public function findNextAddresses(array $available, array $usedRanges, int $howMany = 1): array
    {
        $foundCt = 0;
        $found = [];

        /** @var Range $pool */
        foreach ($available as $pool) {
            foreach ($pool as $newIp) {
                /** @var Range $usedRange */
                foreach ($usedRanges as $usedRange) {
                    if ($usedRange->contains($newIp)) {
                        continue 2;
                    }
                }

                $found[] = $newIp;
                if (++$foundCt === $howMany) { //Found enough, there's still more left in the pool
                    return $found;
                }
            }
        }

        if ($foundCt < $howMany) {
            throw new UnderflowException(
                \sprintf('Not enough addresses available - requested %d, found %d free', $howMany, $foundCt)
            );
        }

        return $found; //Found as many as needed and no more
    }

    /**
     * @return array<Range>
     */
    public function networkToRange(Network ...$networks): array
    {
        $out = [];
        foreach($networks as $network) {
            $out[] = $network->getHosts();
        }

        return $out;
    }

    function addressToNetwork(string $address): Network
    {
        if (\strpos($address, '/') === false) {
            $address .= '/32'; //Single address
        }

        return Network::parse($address);
    }

    /**
     * @return array<Range>
     */
    function getUsedPeersAddresses(Peer ...$peers): array
    {
        $out = [];
        foreach ($peers as $peer) {
            foreach ($peer->allowedAddress as $address) {
                $out[] = $this->addressToNetwork($address)->getHosts();
            }
        }

        return $out;
    }

}

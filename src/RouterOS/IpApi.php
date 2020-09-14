<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\RouterOS;

use IPTools\Network;
use IPTools\Range;
use RouterOS\Query;

class IpApi extends AbstractApi
{
    /**
     * @return array<Network>
     */
    public function getAddressesOnInterface(string $interface): array
    {
        $query = (new Query('/ip/address/print'));
        $query->where('interface', $interface);

        //This is valid in implementation but the interface is broken...
        $result = $this->getClient()->query($query)->read();
        $out = [];
        foreach (\array_column($result, 'address') as $addr) {
            $out[] = $this->networkUtil->addressToNetwork($addr);
        }

        return $out;
    }

    /**
     * @return array<Range>
     */
    public function getIpPool(string $name): array
    {
        $query = (new Query('/ip/pool/print'));
        $query->where('name', $name);

        //This is valid in implementation but the interface is broken...
        $result = $this->getClient()->query($query)->read();
        $out = [];
        foreach (\array_column($result, 'ranges') as $addrList) {
            foreach ($this->rosUtil->listToArray($addrList) as $addr) {
                $out[] = Range::parse($addr);
            }
        }

        return $out;
    }
}

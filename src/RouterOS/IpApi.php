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
        $q = (new Query('/ip/address/print'));
        $q->where('interface', $interface);

        //This is valid in implementation but the interface is broken...
        $r = $this->getClient()->query($q)->read();
        $out = [];
        foreach(array_column($r, 'address') as $addr) {
            $out[] = $this->networkUtil->addressToNetwork($addr);
        }

        return $out;
    }

    /**
     * @return array<Range>
     */
    public function getIpPool(string $name): array
    {
        $q = (new Query('/ip/pool/print'));
        $q->where('name', $name);

        //This is valid in implementation but the interface is broken...
        $r = $this->getClient()->query($q)->read();
        $out = [];
        foreach(array_column($r, 'ranges') as $addrList) {
            foreach($this->rosUtil->listToArray($addrList) as $addr) {
                $out[] = Range::parse($addr);
            }
        }

        return $out;
    }

}

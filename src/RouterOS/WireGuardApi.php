<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\RouterOS;

use NoFlash\ROSAutoWireGuard\Exception\ROSException;
use NoFlash\ROSAutoWireGuard\Struct\Peer;
use RouterOS\Query;

class WireGuardApi extends AbstractApi
{
    public function addPeer(Peer $peer): void
    {
        $q = (new Query('/interface/wireguard/peers/add'));
        foreach ($peer->asSettableArray() as $k => $v) {
            $q->equal($k, $v);
        }

        //Will return ['after' => ['ret' => 'ROS ID']] on success
        //Will return ['after' => ['message' => 'error']] on failure
        $r = $this->getClient()->query($q)->read(); //This is valid in implementation but the interface is broken...

        if (isset($r['after']['ret'])) {
            return;
        }

        if (isset($r['after']['message'])) {
            throw new ROSException('RouterOS error while adding peer: ' . $r['after']['message']);
        }

        throw new ROSException('Unexpected RouterOS response while adding peer: ' . print_r($r, true));
    }

    public function getPeers(?string $interface = null): array
    {
        $q = (new Query('/interface/wireguard/peers/print'));
        if ($interface !== null) {
            $q->where('interface', $interface);
        }

        $r = $this->getClient()->query($q)->read(); //This is valid in implementation but the interface is broken...
        $out = [];
        foreach($r as $apiPeer) {
            $out[] = $peer = new Peer();
            $peer->interface = $apiPeer['interface'];
            $peer->publicKey = $apiPeer['public-key'];
            $peer->endpoint = ($apiPeer['endpoint'] ?? null) ?: null;
            $peer->allowedAddress = $this->rosUtil->listToArray($apiPeer['allowed-address'] ?? '') ?: null;
            $peer->presharedKey = ($apiPeer['preshared-key'] ?? null) ?: null;
            $peer->rx = (int)($apiPeer['rx'] ?? 0);
            $peer->tx = (int)($apiPeer['tx'] ?? 0);
            $peer->lastHandshake = ($apiPeer['last-handshake'] ?? null) ?: null;
            $peer->persistentKeepalive = ((int)($apiPeer['persistent-keepalive'] ?? null)) ?: null;
        }

        return $out;
    }
}

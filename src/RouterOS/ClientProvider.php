<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\RouterOS;

use NoFlash\ROSAutoWireGuard\Exception\LogicException;
use RouterOS\Interfaces\ClientInterface;

/**
 * Goes around Symfony Container limitations (we need to create client after the app is started)
 */
class ClientProvider
{
    private ClientInterface $client;

    public function setClient(ClientInterface $client): void
    {
        if (isset($this->client)) {
            throw new LogicException('Client is already set - you cannot change it'); //prevent weird bugs...
        }

        $this->client = $client;
    }

    public function getClient(): ClientInterface
    {
        if (isset($this->client)) {
            return $this->client;
        }

        throw new LogicException('Client is not set. Did you forget to call setClient()?');
    }
}

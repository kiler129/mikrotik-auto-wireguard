<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\RouterOS;

use NoFlash\ROSAutoWireGuard\NetworkUtil;
use RouterOS\Interfaces\ClientInterface;

abstract class AbstractApi
{
    protected ClientProvider $clientProvider;
    protected ROSUtil $rosUtil;
    protected NetworkUtil $networkUtil;

    public function __construct(ClientProvider $clientProvider, ROSUtil $rosUtil, NetworkUtil $networkUtil)
    {
        $this->clientProvider = $clientProvider;
        $this->rosUtil = $rosUtil;
        $this->networkUtil = $networkUtil;
    }

    protected function getClient(): ClientInterface
    {
        return $this->clientProvider->getClient();
    }
}

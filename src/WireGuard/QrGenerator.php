<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\WireGuard;

use BaconQrCode\Writer;
use NoFlash\ROSAutoWireGuard\WireGuard\Configuration\Configuration;

class QrGenerator
{
    private Writer $qrWriter;

    public function __construct(Writer $qrWriter)
    {
        $this->qrWriter = $qrWriter;
    }

    public function generateConfiguration(Configuration $config): string
    {
        return $this->qrWriter->writeString($config->render());
    }
}

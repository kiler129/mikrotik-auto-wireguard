<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\WireGuard\Configuration;

interface RenderableInterface
{
    public function render(): string;
}

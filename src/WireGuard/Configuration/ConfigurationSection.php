<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\WireGuard\Configuration;

interface ConfigurationSection extends RenderableInterface
{
    public static function getSectionName(): string;
    public function getFields(): array;
}

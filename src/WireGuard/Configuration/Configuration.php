<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\WireGuard\Configuration;

use NoFlash\ROSAutoWireGuard\Exception\SectionAlreadyExistsException;

class Configuration implements RenderableInterface
{
    /**
     * @var ConfigurationSection[]
     */
    private array $sections = [];

    public function addSection(ConfigurationSection $section): void
    {
        $name = $section::getSectionName();
        if (isset($this->sections[$name])) {
            throw new SectionAlreadyExistsException($section);
        }

        $this->sections[$name] = $section;
    }

    public function render(): string
    {
        $out = '';
        foreach ($this->sections as $section) {
            $out .= $section->render();
        }

        return $out;
    }
}

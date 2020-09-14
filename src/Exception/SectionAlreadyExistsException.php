<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\Exception;

use NoFlash\ROSAutoWireGuard\WireGuard\Configuration\ConfigurationSection;

class SectionAlreadyExistsException extends \OverflowException
{
    public function __construct(ConfigurationSection $section)
    {
        parent::__construct(\sprintf('Section "%s" already exists', $section->getSectionName()));
    }
}

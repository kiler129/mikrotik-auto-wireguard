<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\Exception;


class InvalidFieldException extends \InvalidArgumentException
{
    public function __construct(string $field)
    {
        parent::__construct(\sprintf('Field "%s" is invalid', $field));
    }
}

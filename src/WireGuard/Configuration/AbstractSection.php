<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\WireGuard\Configuration;

use NoFlash\ROSAutoWireGuard\Exception\InvalidFieldException;

abstract class AbstractSection implements ConfigurationSection
{
    protected const VALID_FIELDS_MAP = [];

    private array $fields = [];

    public function setField(string $key, string $value): void
    {
        if (!$this->isValidField($key)) {
            throw new InvalidFieldException($key);
        }

        $this->fields[$key] = $value;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function render(): string
    {
        $out = \sprintf("[%s]\n", static::getSectionName());
        foreach ($this->fields as $k => $v) {
            $out .= \sprintf("%s = %s\n", $k, $v);
        }
        $out .= "\n";

        return $out;
    }

    protected function setInsensitiveField(string $name, string $value): void
    {
        $fieldKey = static::VALID_FIELDS_MAP[\strtolower($name)] ?? null;
        if ($fieldKey === null) {
            throw new InvalidFieldException($name);
        }

        $this->setField($fieldKey, $value);
    }

    protected function isValidField(string $name): bool
    {
        return isset(static::VALID_FIELDS_MAP[\strtolower($name)]);
    }

    public function __set(string $name, $value): void
    {
        $this->setInsensitiveField($name, (string)$value);
    }
}

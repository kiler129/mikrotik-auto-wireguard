<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\Struct;

trait ROSStructHelperTrait
{
    /**
     * @return array<string,string>
     */
    public function asSettableArray(): array
    {
        $out = [];
        foreach (static::getApiSettableFields() as $field) {
            if ($this->$field === null) {
                continue;
            }

            $val = $this->$field;
            if (\is_array($val)) {
                $val = \implode(',', $val);
            }

            $out[\strtolower(\preg_replace('/(?<!^)[A-Z]/', '-$0', $field))] = $val;
        }

        return $out;
    }

    /**
     * @return array<string>
     */
    abstract protected static function getApiSettableFields(): array;
}

<?php

namespace Modules\Account\Domain\ValueObjects;

class ActivityId
{
    public int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function isNull(): bool
    {
        return false;
    }

    public function __toString(): string
    {
        return strval($this->value);
    }
}

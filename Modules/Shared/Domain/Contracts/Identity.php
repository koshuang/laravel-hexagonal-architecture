<?php

namespace Modules\Shared\Domain\Contracts;

/** @immutable */
abstract class Identity extends ValueObject implements Nullable
{
    /** @var int|string */
    public $value;

    /**
     * @param  int|string  $value
     */
    public function __construct(mixed $value)
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

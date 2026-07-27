<?php

namespace Modules\Shared\Domain\Contracts;

use Stringable;

/** @immutable */
/** @extends ValueObject<int|string> */
abstract class Identity extends ValueObject implements Nullable, Stringable
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

    public function __toString(): string
    {
        return strval($this->value);
    }

    public function isNull(): bool
    {
        return false;
    }
}

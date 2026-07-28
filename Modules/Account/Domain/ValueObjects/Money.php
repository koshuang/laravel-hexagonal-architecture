<?php

namespace Modules\Account\Domain\ValueObjects;

use Modules\Shared\Domain\Contracts\ValueObject;
use Stringable;

/** @extends ValueObject<self> */
class Money extends ValueObject implements Stringable
{
    public function __construct(
        public int $amount,
    ) {
    }

    public function __toString(): string
    {
        return strval($this->amount);
    }

    public static function ZERO(): Money
    {
        return Money::of(0);
    }

    public static function of(int $amount): Money
    {
        return new Money($amount);
    }

    public static function add(Money $a, Money $b): Money
    {
        return new Money($a->amount + $b->amount);
    }

    public static function subtract(Money $a, Money $b): Money
    {
        return new Money($a->amount - $b->amount);
    }

    public function isPositiveOrZero(): bool
    {
        return $this->amount >= 0;
    }

    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    public function isGreaterThanOrEqualTo(Money $money): bool
    {
        return $this->amount >= $money->amount;
    }

    public function isGreaterThan(Money $money): bool
    {
        return $this->amount > $money->amount;
    }

    public function plus(Money $money): Money
    {
        return new Money($this->amount + $money->amount);
    }

    public function minus(Money $money): Money
    {
        return new Money($this->amount - $money->amount);
    }

    public function negate(): Money
    {
        return new Money(-1 * abs($this->amount));
    }
}

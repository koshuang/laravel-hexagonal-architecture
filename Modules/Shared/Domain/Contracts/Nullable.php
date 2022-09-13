<?php

namespace Modules\Shared\Domain\Contracts;

interface Nullable
{
    public function isNull(): bool;
}

<?php

namespace Modules\Shared\Domain\Contracts;

/**
 * @template T of Identity
 * @extends IdentifiableDomainObject<T>
 */
abstract class Entity extends IdentifiableDomainObject
{
}

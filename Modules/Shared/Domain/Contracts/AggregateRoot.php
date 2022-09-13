<?php

namespace Modules\Shared\Domain\Contracts;

/** @template T */
abstract class AggregateRoot extends Entity
{
    /**
     * @var array<int, DomainEvent>
     */
    public readonly array $domainEvents;

    protected function addDomainEvent(DomainEvent $domainEvent): void
    {
        $this->domainEvents[] = $domainEvent;
    }

    public function clearEvents(): void
    {
        $this->domainEvents = [];
    }
}

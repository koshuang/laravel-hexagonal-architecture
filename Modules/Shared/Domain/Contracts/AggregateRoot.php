<?php

namespace Modules\Shared\Domain\Contracts;

/**
 * @template T
 * @extends Entity<T>
 */
abstract class AggregateRoot extends Entity
{
    /**
     * @var array<int, DomainEvent>
     */
    private array $domainEvents = [];

    public function clearEvents(): void
    {
        $this->domainEvents = [];
    }

    protected function addDomainEvent(DomainEvent $domainEvent): void
    {
        $this->domainEvents[] = $domainEvent;
    }

    /**
     * @return array<int, DomainEvent>
     */
    public function getDomainEvents(): array
    {
        return $this->domainEvents;
    }
}

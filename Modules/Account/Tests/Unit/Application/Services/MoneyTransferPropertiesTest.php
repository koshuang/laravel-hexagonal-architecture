<?php

namespace Modules\Account\Tests\Unit\Application\Services;

use Modules\Account\Application\Services\MoneyTransferProperties;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class MoneyTransferPropertiesTest extends TestCase
{
    #[Test]
    public function it_uses_the_injected_transfer_threshold(): void
    {
        $properties = new MoneyTransferProperties(250000);

        $this->assertSame(250000, $properties->getMaximumTransferThreshold()->amount);
    }
}

<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Tests\Conjoon\Net\Uri\Component\Path;

use Conjoon\Data\Parameter as DataParameter;
use Conjoon\Net\Uri\Component\Path\Parameter;
use Tests\TestCase;

class ParameterTest extends TestCase
{
    public function testClass(): void
    {
        $param = new Parameter("A", "B");
        $this->assertInstanceOf(DataParameter::class, $param);
    }

}

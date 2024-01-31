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

use Conjoon\Data\ParameterList as DataParameterList;
use Conjoon\Net\Uri\Component\Path\Parameter;
use Conjoon\Net\Uri\Component\Path\ParameterList;
use Tests\TestCase;

class ParameterListTest extends TestCase
{
    public function testClass(): void
    {
        $list = new ParameterList();
        $this->assertInstanceOf(DataParameterList::class, $list);
        $this->assertSame(Parameter::class, $list->getEntityType());
    }
}

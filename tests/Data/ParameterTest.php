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

namespace Tests\Conjoon\Data;

use Conjoon\Data\Parameter as DataParameter;

use Conjoon\Net\Uri\Component\Query\Parameter;
use Tests\TestCase;

/**
 * Tests QueryParameter.
 *
 */
class ParameterTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass(): void
    {
        $mock = $this->getQueryParameter("name", "value");

        $this->assertInstanceOf(DataParameter::class, $mock);
    }


    /**
     * @param string $name
     * @param string $value
     * @return Parameter
     */
    protected function getQueryParameter(string $name, string $value): Parameter
    {
        return new Parameter($name, $value);
    }
}

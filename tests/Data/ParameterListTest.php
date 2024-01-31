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

use Conjoon\Core\AbstractList;
use Conjoon\Data\ParameterList;
use Conjoon\Data\Parameter;
use Tests\TestCase;

/**
 * Tests QueryParameterList.
 */
class ParameterListTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------

    /**
     * Tests constructor
     */
    public function testClass(): void
    {

        $list = $this->createList();
        $this->assertInstanceOf(AbstractList::class, $list);

        $this->assertSame(Parameter::class, $list->getEntityType());
    }


    /**
     * Tests to array
     */
    public function testToArray(): void
    {
        $list = $this->createList();

        $this->assertEquals([
            "A" => "B",
            "C" => "D"
        ], $list->toArray());
    }


    /**
     * @return ParameterList
     */
    protected function createList(): ParameterList
    {
        $list = new ParameterList();
        $list[] = new Parameter("A", "B");
        $list[] = new Parameter("C", "D");

        return $list;
    }
}

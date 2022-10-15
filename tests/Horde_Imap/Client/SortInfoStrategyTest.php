<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge,
 * publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
 * USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

declare(strict_types=1);

namespace Tests\Conjoon\Horde_Imap\Client;

use Conjoon\Core\Contract\Arrayable;
use Conjoon\Core\Strategy\JsonStrategy;
use Conjoon\Core\Data\SortDirection;
use Conjoon\Core\Data\SortInfo;
use Conjoon\Core\Data\SortInfoList;
use Conjoon\Horde_Imap\Client\SortInfoStrategy;
use Horde_Imap_Client;
use Tests\TestCase;

/**
 * Tests SortInfoStrategy.
 */
class SortInfoStrategyTest extends TestCase
{
    /**
     * Test inheritance
     */
    public function testClass()
    {
        $strategy = new SortInfoStrategy();
        $this->assertInstanceOf(JsonStrategy::class, $strategy);
    }


    /**
     * Test toJson()
     */
    public function testToJson()
    {
        $strategy = new SortInfoStrategy();

        $this->assertSame([], $strategy->toJson($this->getMockForAbstractClass(Arrayable::class)));


        $sortInfoList = new SortInfoList();
        $sortInfoList[] = new SortInfo("subject", SortDirection::DESC);
        $sortInfoList[] = new SortInfo("date", SortDirection::ASC);
        $sortInfoList[] = new SortInfo("size", SortDirection::DESC);

        $strategy = new SortInfoStrategy();
        $strategy->toJson($sortInfoList);

        $result = [
           Horde_Imap_Client::SORT_REVERSE,
           Horde_Imap_Client::SORT_SUBJECT,
           Horde_Imap_Client::SORT_DATE,
           Horde_Imap_Client::SORT_REVERSE,
           Horde_Imap_Client::SORT_SIZE
         ];

        $this->assertSame(
            $result,
            $strategy->toJson($sortInfoList)
        );
    }
}

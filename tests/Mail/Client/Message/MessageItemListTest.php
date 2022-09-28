<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2019-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Tests\Conjoon\Mail\Client\Message;

use Conjoon\Core\Contract\Arrayable;
use Conjoon\Mail\Client\Data\CompoundKey\MessageKey;
use Conjoon\Mail\Client\Message\MessageItemList;
use Conjoon\Mail\Client\Message\MessageItem;
use Conjoon\Core\Data\AbstractList;
use Tests\JsonableTestTrait;
use Tests\TestCase;

/**
 * Class MessageItemListTest
 * @package Tests\Conjoon\Mail\Client\Message
 */
class MessageItemListTest extends TestCase
{
    use JsonableTestTrait;

// ---------------------
//    Tests
// ---------------------

    /**
     * Tests constructor
     */
    public function testClass()
    {

        $messageItemList = $this->createList();
        $this->assertInstanceOf(AbstractList::class, $messageItemList);
        $this->assertInstanceOf(Arrayable::class, $messageItemList);
        $this->assertSame(MessageItem::class, $messageItemList->getEntityType());

        $this->assertSame([
            $messageItemList[0]->toArray(),
            $messageItemList[1]->toArray()
        ], $messageItemList->toArray());
    }


    public function testToJson()
    {
        $messageItemList = $this->createList();
        $this->runToJsonTest($messageItemList);
    }


    protected function createList()
    {
        $messageItemList = new MessageItemList();
        $messageItemList[] = new MessageItem(
            new MessageKey("dev", "INBOX", "1"),
            null
        );
        $messageItemList[] = new MessageItem(
            new MessageKey("dev", "INBOX", "2"),
            null
        );

        return $messageItemList;
    }
}

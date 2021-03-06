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

use Conjoon\Mail\Client\Data\CompoundKey\MessageKey;
use Conjoon\Mail\Client\Message\AbstractMessageItem;
use Conjoon\Mail\Client\Message\MessageItem;
use Tests\TestCase;
use TypeError;

/**
 * Class MessageItemTest
 * @package Tests\Conjoon\Mail\Client\Message
 */
class MessageItemTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------

    /**
     * Tests constructor
     */
    public function testConstructor()
    {

        $messageKey = $this->createMessageKey();
        $messageItem = $this->createMessageItem($messageKey);
        $this->assertInstanceOf(AbstractMessageItem::class, $messageItem);

        $this->assertSame($messageKey, $messageItem->getMessageKey());
    }

    /**
     * Test type exceptions.
     */
    public function testTypeException()
    {

        $caught = [];

        $testException = function ($key, $type) use (&$caught) {

            $item = $this->getItemConfig();

            switch ($type) {
                case "int":
                    $item[$key] = (int)$item[$key];
                    break;
                case "string":
                    $item[$key] = (string)$item[$key];
                    break;

                default:
                    $item[$key] = $type;
                    break;
            }

            try {
                $this->createMessageItem($this->createMessageKey(), $item);
            } catch (TypeError $e) {
                if (in_array($e->getMessage(), $caught)) {
                    return;
                }
                $caught[] = $e->getMessage();
            }
        };

        $testException("hasAttachments", "string");
        $testException("size", "string");

        $this->assertSame(2, count($caught));
    }


    /**
     * toJson()
     */
    public function testToJson()
    {

        $messageKey = $this->createMessageKey();
        $item = $this->createMessageItem($this->createMessageKey(), $this->getItemConfig());

        $json = $item->toJson();

        $expected = array_merge($messageKey->toJson(), $this->getItemConfig());
        unset($expected["charset"]);

        ksort($expected);

        $this->assertSame(
            $expected,
            $json
        );
    }

// ---------------------
//    Helper Functions
// ---------------------

    /**
     * Returns an MessageItem as array.
     */
    protected function getItemConfig(): array
    {

        return [
            "charset" => "ISO-8859-1",
            "size" => 83,
            "hasAttachments" => true
        ];
    }


    /**
     * Returns an anonymous class extending AbstractMessageItem.
     * @param MessageKey $key
     * @param array|null $data
     * @return AbstractMessageItem
     */
    protected function createMessageItem(MessageKey $key, array $data = null): AbstractMessageItem
    {
        // Create a new instance from the Abstract Class
        return new MessageItem($key, $data);
    }


    /**
     * Returns a MessageKey.
     *
     * @param string $mailAccountId
     * @param string $mailFolderId
     * @param string $id
     *
     * @return MessageKey
     */
    protected function createMessageKey(
        string $mailAccountId = "dev",
        string $mailFolderId = "INBOX",
        string $id = "232"
    ): MessageKey {
        return new MessageKey($mailAccountId, $mailFolderId, $id);
    }
}

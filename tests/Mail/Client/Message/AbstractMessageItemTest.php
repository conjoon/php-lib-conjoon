<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2020-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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
use Conjoon\Mail\Client\Data\MailAddress;
use Conjoon\Mail\Client\Data\MailAddressList;
use Conjoon\Mail\Client\MailClientException;
use Conjoon\Mail\Client\Message\AbstractMessageItem;
use Conjoon\Mail\Client\Message\Flag\DraftFlag;
use Conjoon\Mail\Client\Message\Flag\FlaggedFlag;
use Conjoon\Mail\Client\Message\Flag\FlagList;
use Conjoon\Mail\Client\Message\Flag\SeenFlag;
use Conjoon\Util\Jsonable;
use Conjoon\Util\Modifiable;
use DateTime;
use Tests\TestCase;
use TypeError;

/**
 * Class AbstractMessageItemTest
 * @package Tests\Conjoon\Mail\Client\Message
 */
class AbstractMessageItemTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------

    /**
     * Tests constructor
     */
    public function testConstructor()
    {

        $messageItem = $this->createMessageItem();
        $this->assertInstanceOf(Jsonable::class, $messageItem);
        $this->assertInstanceOf(Modifiable::class, $messageItem);

        $this->assertNull($messageItem->getSeen());
        $this->assertNull($messageItem->getFlagged());
        $this->assertNull($messageItem->getDraft());

        $this->assertNull($messageItem->getCharset());

        $this->assertNull($messageItem->getMessageId());
        $this->assertNull($messageItem->getReferences());
        $this->assertNull($messageItem->getinReplyTo());
    }


    /**
     * Test class.
     */
    public function testClass()
    {

        $item = $this->getItemConfig();

        $messageItem = $this->createMessageItem(null, $item);

        $this->assertSame([], $messageItem->getModifiedFields());

        foreach ($item as $key => $value) {
            $method = "get" . ucfirst($key);

            switch ($key) {
                case "date":
                    $this->assertNotSame($item["date"], $messageItem->getDate());
                    $this->assertEquals($item["date"], $messageItem->getDate());
                    break;
                case "from":
                    $this->assertNotSame($item["from"], $messageItem->getFrom());
                    $this->assertEquals($item["from"], $messageItem->getFrom());
                    break;
                case "to":
                    $this->assertNotSame($item["to"], $messageItem->getTo());
                    $this->assertEquals($item["to"], $messageItem->getTo());
                    break;
                default:
                    $this->assertSame($messageItem->{$method}(), $value, $key);
            }
        }
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
                $this->createMessageItem(null, $item);
            } catch (TypeError $e) {
                if (in_array($e->getMessage(), $caught)) {
                    return;
                }
                $caught[] = $e->getMessage();
            }
        };

        $testException("subject", "int");
        $testException("seen", "string");
        $testException("answered", "string");
        $testException("recent", "string");
        $testException("draft", "string");
        $testException("flagged", "string");
        $testException("from", "");
        $testException("to", "");
        $testException("date", "");
        $testException("charset", "int");
        $testException("messageId", "string");
        $testException("inReplyTo", "string");
        $testException("references", "string");

        $this->assertSame(10, count($caught));
    }


    /**
     * Test toJson
     */
    public function testToJson()
    {
        $key = $this->createMessageKey();
        $item = array_merge(
            $key->toJson(),
            $this->getItemConfig(true)
        );
        unset($item["charset"]);
        unset($item["inReplyTo"]);
        ksort($item);

        $messageItem = $this->createMessageItem($key, $this->getItemConfig());
        $json = $messageItem->toJson();

        $this->assertSame($item, $json);

        $messageItem = $this->createMessageItem();

        $json = $messageItem->toJson();

        $this->assertArrayNotHasKey("data", $json);
        $this->assertArrayNotHasKey("to", $json);
        $this->assertArrayNotHasKey("from", $json);
    }


    /**
     * Test setFrom /w null
     */
    public function testSetFromWithNull()
    {

        $messageItem = $this->createMessageItem(null, ["from" => null]);

        $this->assertSame(null, $messageItem->getFrom());
    }

    /**
     * getFlagList()
     */
    public function testGetFlagList()
    {

        $item = $this->createMessageItem(null, $this->getItemConfig());

        $flagList = $item->getFlagList();

        $this->assertInstanceOf(FlagList::class, $flagList);

        $caught = 0;

        foreach ($flagList as $flag) {
            switch (true) {
                case ($flag instanceof SeenFlag):
                case ($flag instanceof DraftFlag):
                    if ($flag->getValue() === false) {
                        $caught++;
                    }
                    break;

                case ($flag instanceof FlaggedFlag):
                    if ($flag->getValue() === true) {
                        $caught++;
                    }
                    break;
            }
        }

        $this->assertSame(3, $caught);
    }


    /**
     * getFlagList()
     */
    public function testGetFlagListEmpty()
    {

        $item = $this->createMessageItem();

        $flagList = $item->getFlagList();

        $this->assertTrue(count($flagList) === 0);
    }


    /**
     * Tests modifiable
     */
    public function testModifiable()
    {

        $messageKey = $this->createMessageKey();
        $messageItem = $this->createMessageItem($messageKey);

        $conf = $this->getItemConfig();
        $mod = [];
        $it = 0;

        $skipFields = ["messageId"];

        $fieldLength = count(array_keys($conf));
        $this->assertTrue($fieldLength > 0);

        $this->assertSame($mod, $messageItem->getModifiedFields());
        foreach ($conf as $field => $value) {
            if (in_array($field, $skipFields)) {
                continue;
            }
            $messageItem->{"set" . ucfirst($field)}($value);
            $mod[] = $field;
            $this->assertSame($mod, $messageItem->getModifiedFields());
            $it++;
        }
        $this->assertSame($fieldLength - count($skipFields), $it);
    }


    /**
     * Tests isHeaderField
     */
    public function testIsHeaderField()
    {

        $fields = ["from", "to", "subject", "date", "references", "inReplyTo"];

        foreach ($fields as $field) {
            $this->assertTrue(AbstractMessageItem::isHeaderField($field));
        }

        $fields = ["recent", "seen", "flagged", "answered", "messageId"];

        foreach ($fields as $field) {
            $this->assertFalse(AbstractMessageItem::isHeaderField($field));
        }
    }

    /**
     * Tests setMessageItem
     */
    public function testSetMessageItem()
    {

        $item = $this->createMessageitem(null, ["messageId" => "mid"]);

        $this->assertSame($item->getMessageId(), "mid");

        $exp = null;
        try {
            $item->setMessageId("foo");
        } catch (MailClientException $e) {
            $exp = $e;
        }

        $this->assertNotNull($exp);
    }


    /**
     * Tests setting various fields to null or empty string
     */
    public function testSetNullOrEmptyStringFields()
    {

        $item = $this->createMessageitem(null, ["messageId" => "mid"]);

        $fields = ["references", "inReplyTo"];

        foreach ($fields as $field) {
            $getter = "get" . ucfirst($field);
            $setter = "set" . ucfirst($field);

            $this->assertNull($item->{$getter}());
            $item->{$setter}(null);
            $this->assertNull($item->{$getter}());

            $item->{$setter}("");
            $this->assertSame("", $item->{$getter}());
        }
    }


    /**
     * Tests setReferences w/ exception
     */
    public function testSetReferencesException()
    {

        $item = $this->createMessageitem(null, $this->getItemConfig());

        $this->expectException(MailClientException::class);

        $item->setReferences("");
    }


    /**
     * Tests setInReplyTo w/ exception
     */
    public function testSetInReplyToException()
    {

        $item = $this->createMessageitem(null, $this->getItemConfig());

        $this->expectException(MailClientException::class);

        $item->setInReplyTo("");
    }

// ---------------------
//    Helper Functions
// ---------------------


    /**
     * Returns an anonymous class extending AbstractMessageItem.
     *
     * @param MessageKey|null $key
     * @param array|null $data
     * @return AbstractMessageItem
     * @parsm MessageKey $key
     *
     */
    protected function createMessageItem(MessageKey $key = null, array $data = null): AbstractMessageItem
    {
        // Create a new instance from the Abstract Class
        if (!$key) {
            $key = $this->createMessageKey();
        }
        return new class ($key, $data) extends AbstractMessageItem {
        };
    }


    /**
     * Returns a MessageKey
     *
     * @return MessageKey
     */
    protected function createMessageKey(): MessageKey
    {
        // Create a new instance from the Abstract Class
        return new MessageKey("a", "b", "c");
    }


    /**
     * Returns an MessageItem as array.
     */
    protected function getItemConfig($asJson = false): array
    {

        return [
            "from" => $asJson ? $this->createFrom()->toJson() : $this->createFrom(),
            "to" => $asJson ? $this->createTo()->toJson() : $this->createTo(),
            "subject" => "SUBJECT",
            "date" => $asJson ? (new DateTime())->format("Y-m-d H:i:s O") : new DateTime(),
            "seen" => false,
            "answered" => true,
            "draft" => false,
            "flagged" => true,
            "recent" => false,
            "charset" => "ISO-8859-1",
            "messageId" => "mid",
            "inReplyTo" => "midrt",
            "references" => "midr"
        ];
    }


    /**
     * Returns a MailAddress to be used with the "from" property of the MessageItem
     * to test.
     *
     * @return MailAddress
     */
    protected function createFrom(): MailAddress
    {
        return new MailAddress("peterParker@newyork.com", "Peter Parker");
    }

    /**
     * Returns a MailAddressList to be used with the "to" property of the MessageItem
     * @return MailAddressList
     */
    protected function createTo(): MailAddressList
    {

        $list = new MailAddressList();

        $list[] = new MailAddress("name1", "name1@address.testcomdomaindev");
        $list[] = new MailAddress("name2", "name2@address.testcomdomaindev");

        return $list;
    }
}

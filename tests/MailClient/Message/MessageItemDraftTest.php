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

namespace Tests\Conjoon\MailClient\Message;

use Conjoon\MailClient\Data\CompoundKey\MessageKey;
use Conjoon\MailClient\Data\MailAddress;
use Conjoon\MailClient\Data\MailAddressList;
use Conjoon\MailClient\Exception\MailClientException;
use Conjoon\MailClient\Message\AbstractMessageItem;
use Conjoon\MailClient\Message\DraftTrait;
use Conjoon\MailClient\Message\MessageItemDraft;
use Conjoon\Data\Modifiable;
use Tests\TestCase;
use TypeError;

/**
 * Class MessageItemDraftTest
 * @package Tests\Conjoon\MailClient\Message
 */
class MessageItemDraftTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------

    /**
     * Tests instance
     */
    public function testConstructor()
    {
        $uses = class_uses(MessageItemDraft::class);
        $this->assertContains(DraftTrait::class, $uses);

        $messageItem = $this->createMessageItem();
        $this->assertInstanceOf(AbstractMessageItem::class, $messageItem);
        $this->assertInstanceOf(Modifiable::class, $messageItem);

        $this->assertTrue($messageItem->getDraft());
    }


    /**
     * Tests setMessageKey
     */
    public function testSetMessageKey()
    {

        $messageKey = $this->createMessageKey("a", "b", "c");
        $messageItem = $this->createMessageItem(null, $this->getItemConfig());

        $newItem = $messageItem->setMessageKey($messageKey);
        $this->assertSame($messageKey, $newItem->getMessageKey());
        $this->assertNotSame($messageKey, $messageItem->getMessageKey());
        $this->assertNotSame($newItem, $messageItem);

        $this->assertSame([], $messageItem->getModifiedFields());
        $this->assertSame([], $newItem->getModifiedFields());

        $newItem->toArray();
        $oldJson = $messageItem->toArray();

        $expCaught = count(array_keys($oldJson)) - 3; // w/o messageKey data

        $caught = 0;

        foreach ($oldJson as $key => $value) {
            $getter = "get" . ucfirst($key);

            if (in_array($key, ["id", "mailAccountId", "mailFolderId"])) {
                continue;
            }

            if (in_array($key, ["from", "replyTo", "to", "cc", "bcc"])) {
                $this->assertNotSame($newItem->{$getter}(), $messageItem->{$getter}());
                $this->assertEquals($newItem->{$getter}(), $messageItem->{$getter}());
            } else {
                $this->assertSame($newItem->{$getter}(), $messageItem->{$getter}());
            }
            $caught++;
        }

        $this->assertTrue($expCaught > 0);
        $this->assertSame($caught, $expCaught);
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
                /** @noinspection PhpParamsInspection */
                $this->createMessageItem($item);
            } catch (TypeError $e) {
                if (in_array($e->getMessage(), $caught)) {
                    return;
                }
                $caught[] = $e->getMessage();
            }
        };

        $testException("draft", "string");

        $this->assertSame(1, count($caught));
    }


    /**
     * Test toArray
     */
    public function testToArray()
    {
        $item = $this->getItemConfig();

        $messageKey = $this->createMessageKey();

        $messageItem = $this->createMessageItem($messageKey, $item);

        $keys = array_keys($item);

        $skipFields = ["draftInfo"];

        foreach ($keys as $key) {
            if (in_array($key, $skipFields)) {
                continue;
            }
            if (in_array($key, ["from", "replyTo", "to", "cc", "bcc"])) {
                $this->assertEquals($item[$key]->toArray(), $messageItem->toArray()[$key]);
            } else {
                $this->assertSame($item[$key], $messageItem->toArray()[$key]);
            }
        }


        $messageKey = $this->createMessageKey();
        $messageItem = $this->createMessageItem($messageKey, $item);


        $json = $messageItem->toArray();
        $keyJson = $messageKey->toArray();

        $this->assertSame($json["id"], $keyJson["id"]);
        $this->assertSame($json["mailAccountId"], $keyJson["mailAccountId"]);
        $this->assertSame($json["mailFolderId"], $keyJson["mailFolderId"]);

        $messageItem = $this->createMessageItem();
        $json = $messageItem->toArray();

        $this->assertArrayNotHasKey("replyTo", $json);
        $this->assertArrayNotHasKey("cc", $json);
        $this->assertArrayNotHasKey("bcc", $json);
    }


    /**
     * Tests modifiable
     */
    public function testModifiable()
    {

        $messageKey = $this->createMessageKey();
        $messageItem = $this->createMessageItem($messageKey);

        $this->assertSame([], $messageItem->getModifiedFields());
        $messageItem->setCc($this->getItemConfig()["cc"]);
        $this->assertSame(["cc"], $messageItem->getModifiedFields());

        $messageItem->setBcc($this->getItemConfig()["bcc"]);
        $this->assertSame(["cc", "bcc"], $messageItem->getModifiedFields());

        $messageItem->setReplyTo($this->getItemConfig()["replyTo"]);
        $this->assertSame(["cc", "bcc", "replyTo"], $messageItem->getModifiedFields());
    }


    /**
     * Tests various nullable address header fields
     */
    public function testAddressesNull()
    {

        $messageItem = $this->createMessageItem(null, $this->getItemConfig());

        $modified = $messageItem->getModifiedFields();
        $this->assertFalse(in_array("from", $modified));
        $this->assertFalse(in_array("replyTo", $modified));
        $this->assertFalse(in_array("to", $modified));
        $this->assertFalse(in_array("cc", $modified));
        $this->assertFalse(in_array("bcc", $modified));

        $messageItem->setFrom();
        $messageItem->setReplyTo();
        $messageItem->setTo();
        $messageItem->setCc();
        $messageItem->setBcc();

        $json = $messageItem->toArray();

        $this->assertArrayNotHasKey("from", $json);
        $this->assertArrayNotHasKey("replyTo", $json);
        $this->assertArrayNotHasKey("to", $json);
        $this->assertArrayNotHasKey("cc", $json);
        $this->assertArrayNotHasKey("bcc", $json);

        $modified = $messageItem->getModifiedFields();
        $this->assertTrue(in_array("from", $modified));
        $this->assertTrue(in_array("replyTo", $modified));
        $this->assertTrue(in_array("to", $modified));
        $this->assertTrue(in_array("cc", $modified));
        $this->assertTrue(in_array("bcc", $modified));
    }


    /**
     * Tests isHeaderField
     */
    public function testIsHeaderField()
    {

        $fields = ["from", "to", "subject", "date", "replyTo", "cc", "bcc"];

        foreach ($fields as $field) {
            $this->assertTrue(MessageItemDraft::isHeaderField($field));
        }

        $fields = ["recent", "seen", "flagged", "answered", "draft", "foobar"];

        foreach ($fields as $field) {
            $this->assertFalse(MessageItemDraft::isHeaderField($field));
        }
    }


    /**
     * Tests draftInfo Field w/ setters and getters
     */
    public function testXCnDraftInfo()
    {

        $draft = $this->createMessageItem(null, $this->getItemConfig());
        $this->assertSame($draft->getDraftInfo(), $this->getItemConfig()["draftInfo"]);


        $draft = $this->createMessageItem(null, ["messageId" => "mid"]);
        $val = "draftinfo";
        $draft->setDraftInfo($val);
        $this->assertSame($draft->getDraftInfo(), $val);

        $draft = $this->createMessageItem(null, ["messageId" => "mid"]);
        $draft->setDraftInfo();
        $this->assertNull($draft->getDraftInfo());
    }


    /**
     * Tests setting draftInfo if already set
     */
    public function testSetDraftInfoException()
    {

        $draft = $this->createMessageItem(null, $this->getItemConfig());

        $this->assertNotNull($draft->getDraftInfo());

        $this->expectException(MailClientException::class);

        $draft->setDraftInfo("foo");
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
            "from" => $this->createFrom(),
            "replyTo" => $this->createReplyTo(),
            "to" => $this->createTo(),
            "cc" => $this->createCc(),
            "bcc" => $this->createBcc(),
            "draft" => true,
            "draftInfo" => "WyJzaXRlYXJ0d29yayIsIklOQk9YIiwiMTU5NzUyIl0="
        ];
    }


    /**
     * Returns a MessageItemDraft.
     *
     * @param MessageKey|null $key
     * @param array|null $data
     * @return MessageItemDraft
     */
    protected function createMessageItem(MessageKey $key = null, array $data = null): MessageItemDraft
    {
        if (!$key) {
            $key = $this->createMessageKey();
        }

        return new MessageItemDraft($key, $data);
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

    /**
     * Returns a MailAddress to be used with the "from" property of the MessageItem
     * to test.
     *
     * @return MailAddress
     */
    protected function createFrom(): MailAddress
    {
        return new MailAddress("from@from.com", "From From");
    }


    /**
     * Returns a MailAddress to be used with the "replyTo" property of the MessageItem
     * to test.
     *
     * @return MailAddress
     */
    protected function createReplyTo(): MailAddress
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

        $list[] = new MailAddress("to1", "to1@address.testcomdomaindev");
        $list[] = new MailAddress("to2", "to2@address.testcomdomaindev");

        return $list;
    }

    /**
     * Returns a MailAddressList to be used with the "cc" property of the MessageItem
     * @return MailAddressList
     */
    protected function createCc(): MailAddressList
    {

        $list = new MailAddressList();

        $list[] = new MailAddress("name1", "name1@address.testcomdomaindev");
        $list[] = new MailAddress("name2", "name2@address.testcomdomaindev");

        return $list;
    }

    /**
     * Returns a MailAddressList to be used with the "bcc" property of the MessageItem
     * @return MailAddressList
     */
    protected function createBcc(): MailAddressList
    {

        $list = new MailAddressList();

        $list[] = new MailAddress("name1", "name1@address.testcomdomaindev");
        $list[] = new MailAddress("name2", "name2@address.testcomdomaindev");

        return $list;
    }
}

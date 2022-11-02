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

namespace Tests\Conjoon\MailClient\Message;

use Conjoon\MailClient\Data\CompoundKey\MessageKey;
use Conjoon\MailClient\Data\MailAddress;
use Conjoon\MailClient\Data\MailAddressList;
use Conjoon\MailClient\Message\AbstractMessageItem;
use Conjoon\MailClient\Message\DraftTrait;
use Conjoon\MailClient\Message\ListMessageItem;
use Conjoon\MailClient\Message\MessageItem;
use Conjoon\MailClient\Message\MessagePart;
use Tests\TestCase;

/**
 * Class ListMessageItemTest
 * @package Tests\Conjoon\MailClient\Message
 */
class DraftTraitTest extends TestCase
{
    public function testTrait()
    {
        $trait = $this->getMockedTrait();

        $list = $this->createAddresses();
        $trait->setCc($list);
        $this->assertNotFalse(array_search("cc", $trait->getModifiedFields()));
        $this->assertNotSame($list, $trait->getCc());

        $list = $this->createAddresses();
        $trait->setBcc($list);
        $this->assertNotFalse(array_search("bcc", $trait->getModifiedFields()));
        $this->assertNotSame($list, $trait->getBcc());

        $address = $this->createAddress();
        $trait->setReplyTo($address);
        $this->assertNotFalse(array_search("replyTo", $trait->getModifiedFields()));
        $this->assertNotSame($address, $trait->getReplyTo());

        $this->assertTrue($trait::isHeaderField("cc"));
        $this->assertTrue($trait::isHeaderField("bcc"));
        $this->assertTrue($trait::isHeaderField("replyTo"));
    }


// ---------------------
//    Helper Functions
// ---------------------

    /**
     * @return MailAddressList
     */
    protected function createAddresses(): MailAddressList
    {

        $list = new MailAddressList();

        $list[] = $this->createAddress();

        return $list;
    }


    /**
     * @return MailAddress
     */
    protected function createAddress(): MailAddress
    {
        return new MailAddress("name1", "name1@address.testcomdomaindev");
    }


    /**
     * @return MockTrait
     */
    public function getMockedTrait()
    {
        return new MockTrait(new MessageKey("1", "2", "3"));
    }
}

// phpcs:disable
class MockTrait extends AbstractMessageItem
{
    use DraftTrait;
}
// phpcs:enable

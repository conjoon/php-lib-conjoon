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

namespace Tests\Conjoon\MailClient\Data;

use Conjoon\MailClient\Data\MailAddress;
use Conjoon\MailClient\Data\MailAddressList;
use Conjoon\Core\AbstractList;
use Conjoon\Core\Contract\Copyable;
use Conjoon\Core\Contract\JsonDecodable;
use Conjoon\Core\Exception\JsonDecodeException;
use Tests\JsonableTestTrait;
use Tests\StringableTestTrait;
use Tests\TestCase;

/**
 * Class MailAddressListTest
 * @package Tests\Conjoon\MailClient\Data
 */
class MailAddressListTest extends TestCase
{
    use JsonableTestTrait;
    use StringableTestTrait;

// ---------------------
//    Tests
// ---------------------

    /**
     * Tests constructor
     */
    public function testClass()
    {

        $mailAddressList = new MailAddressList();
        $this->assertInstanceOf(AbstractList::class, $mailAddressList);
        $this->assertInstanceOf(JsonDecodable::class, $mailAddressList);
        $this->assertInstanceOf(Copyable::class, $mailAddressList);


        $this->assertSame(MailAddress::class, $mailAddressList->getEntityType());
    }



    /**
     * Tests toArray
     */
    public function testToArray()
    {
        $mailAddressList = $this->createList();

        $this->assertEquals([
            $mailAddressList[0]->toArray(),
            $mailAddressList[1]->toArray()
        ], $mailAddressList->toArray());
    }


    /**
     * fromJsonString
     */
    public function testFromString()
    {
        $mailAddressList = $this->createList();

        $jsonString = json_encode($mailAddressList->toArray());
        $this->assertEquals($mailAddressList, MailAddressList::fromString($jsonString));

        $jsonString = "[{\"name\" : \"foo\"}]";
        $this->assertEmpty(MailAddressList::fromString($jsonString));
    }


    /**
     * expect JsonDecodeException
     */
    public function testFromStringWithException()
    {
        $this->expectException(JsonDecodeException::class);
        $jsonString = json_encode([]);
        MailAddressList::fromString($jsonString);
    }


    /**
     * expect JsonDecodeException
     */
    public function testFromStringWithExceptionInvalid()
    {
        $this->expectException(JsonDecodeException::class);
        $jsonString = "{json:1)";
        MailAddressList::fromString($jsonString);
    }


    /**
     * toString
     */
    public function testToString()
    {
        $mailAddressList = $this->createList();

        $this->assertSame(
            $mailAddressList[0]->toString() . ", " . $mailAddressList[1]->toString(),
            $mailAddressList->toString()
        );

        $this->runToStringTest(MailAddressList::class);
    }

    /**
     *
     */
    public function testCopy()
    {
        $mailAddressList = $this->createList();

        $copy = $mailAddressList->copy();
        $this->assertEquals($copy, $mailAddressList);
        $this->assertNotSame($copy, $mailAddressList);
    }


    /**
     * tests toJson
     */
    public function testToJson()
    {
        $mailAddressList = $this->createList();
        $this->runToJsonTest($mailAddressList);
    }


// +----------------------------------------
// | Helper
// +----------------------------------------
    /**
     * @return MailAddressList
     */
    protected function createList()
    {
        $mailAddressList = new MailAddressList();
        $mailAddressList[] = new MailAddress("name1@address.testcomdomaindev", "name1");
        $mailAddressList[] = new MailAddress("name2@address.testcomdomaindev", "name2");

        return $mailAddressList;
    }
}
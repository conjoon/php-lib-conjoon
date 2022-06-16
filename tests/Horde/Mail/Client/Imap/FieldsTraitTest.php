<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2021-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Tests\Conjoon\Horde\Mail\Client\Imap;

use Conjoon\Horde\Mail\Client\Imap\AttributeTrait;
use Conjoon\Horde\Mail\Client\Imap\FieldsTrait;
use ReflectionClass;
use Tests\TestCase;

/**
 * Class FieldsTraitTest
 * @package Tests\Conjoon\Horde\Mail\Client\Imap
 */
class FieldsTraitTest extends TestCase
{
    /**
     * getDefAttr()
     * @throws ReflectionException
     */
    public function testGetDefAttr()
    {
        $client = $this->getMockedTrait();

        foreach (["MessageItem", "MailFolder"] as $type) {
            $defs = array_map(fn ($item) => true, array_flip($client->getDefaultFields($type)));

            $reflection = new ReflectionClass($client);
            $property = $reflection->getMethod("getDefFields");
            $property->setAccessible(true);

            $this->assertEquals(
                $defs,
                $property->invokeArgs($client, [$type, ])
            );

            $this->assertEquals(
                array_merge($defs, ["foo" => true]),
                $property->invokeArgs($client, [$type, ["foo" => []]])
            );

            $this->assertEquals(
                array_merge($defs, ["foo" => ["length" => 3]]),
                $property->invokeArgs($client, [$type, ["foo" => ["length" => 3]]])
            );

            $this->assertEquals(
                $defs,
                $property->invokeArgs($client, [$type, ["foo" => false]])
            );
        }
    }

    /**
     * tests getSupportedFields()
     */
    public function testGetSupportedFields()
    {
        $client = $this->getMockedTrait();
        $this->assertEquals([
            "hasAttachments",
            "size",
            "plain", // \ __Preview
            "html",  // /   Text
            "cc",
            "bcc",
            "replyTo",
            "from",
            "to",
            "subject",
            "date",
            "seen",
            "answered",
            "draft",
            "flagged",
            "recent",
            "charset",
            "references",
            "messageId"
        ], $client->getSupportedFields("MessageItem"));

        $this->assertEquals([
            "name",
            "unreadMessages",
            "totalMessages"
        ], $client->getSupportedFields("MailFolder"));
    }


    /**
     * tests getDefaultFields()
     */
    public function testGetDefaultFields()
    {
        $client = $this->getMockedTrait();
        $this->assertEquals([
            "from",
            "to",
            "subject",
            "date",
            "seen",
            "answered",
            "draft",
            "flagged",
            "recent",
            "charset",
            "references",
            "messageId",
            "plain",
            "size",
            "hasAttachments"
        ], $client->getDefaultFields("MessageItem"));

        $this->assertEquals([
            "name",
            "unreadMessages",
            "totalMessages"
        ], $client->getDefaultFields("MailFolder"));
    }


    /**
     * tests getField()
     * @throws ReflectionException
     */
    public function testGetField()
    {
        $client = $this->getMockedTrait();

        $reflection = new ReflectionClass($client);
        $property = $reflection->getMethod("getField");
        $property->setAccessible(true);

        $this->assertEquals(
            true,
            $property->invokeArgs($client, ["foo", ["foo" => true]])
        );

        $this->assertEquals(
            true,
            $property->invokeArgs($client, ["foo", ["foo" => []], "snafu"])
        );

        $this->assertEquals(
            null,
            $property->invokeArgs($client, ["foo", ["bar" => true]])
        );

        $this->assertEquals(
            null,
            $property->invokeArgs($client, ["foo", ["foo" => false]])
        );

        $this->assertEquals(
            "default",
            $property->invokeArgs($client, ["foo", ["foo" => false], "default"])
        );
    }



    /**
     * @return __anonymous@1559
     */
    public function getMockedTrait()
    {
        return new class (){
            use FieldsTrait;
        };
    }
}

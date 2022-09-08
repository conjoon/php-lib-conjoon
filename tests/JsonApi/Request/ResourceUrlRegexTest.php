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

namespace Tests\Conjoon\JsonApi\Request;

use Conjoon\Core\Contract\Arrayable;
use Conjoon\JsonApi\Request\ResourceUrlRegex;
use ReflectionException;
use Tests\TestCase;

/**
 * tests ResourceUrlRegexList
 */
class ResourceUrlRegexTest extends TestCase
{
    /**
     * Tests functionality
     */
    public function testClass()
    {
        $regex = "/MailAccounts\/.+\/MailFolders\/.+\/(MessageItems)(\/*.*$)/m";
        $resourceUrlRegex = $this->getMockBuilder(ResourceUrlRegex::class)
                         ->enableOriginalConstructor()
                         ->setConstructorArgs([$regex, 1, 2])
                         ->enableProxyingToOriginalMethods()
                         ->onlyMethods(["normalizeName"])
                         ->getMock();

        $this->assertInstanceOf(Arrayable::class, $resourceUrlRegex);

        $this->assertSame($regex, $resourceUrlRegex->getRegex());
        $this->assertSame(1, $resourceUrlRegex->getNameIndex());
        $this->assertSame(2, $resourceUrlRegex->getSingleIndex());

        $resourceUrlRegex->expects($this->once())->method("normalizeName")->with("MessageItems");
        $this->assertSame(
            "MessageItem",
            $resourceUrlRegex->getResourceName("/MailAccounts/dev/MailFolders/INBOX/MessageItems/1")
        );

        $this->assertTrue(
            $resourceUrlRegex->isSingleRequest("/MailAccounts/dev/MailFolders/INBOX/MessageItems/1")
        );

        $this->assertFalse(
            $resourceUrlRegex->isSingleRequest("/MailAccounts/dev/MailFolders/INBOX/MessageItems")
        );
    }


    /**
     * Tests normalizeName
     * @throws ReflectionException
     */
    public function testNormalizeName()
    {
        $resourceUrlRegex = new ResourceUrlRegex("", 1, 2);

        $normalizeName = $this->makeAccessible($resourceUrlRegex, "normalizeName");

        $this->assertSame(
            "MessageBody",
            $normalizeName->invokeArgs($resourceUrlRegex, ["MessageBodies"])
        );

        $this->assertSame(
            "Address",
            $normalizeName->invokeArgs($resourceUrlRegex, ["Addresses"])
        );
    }


    /**
     * Tests singleIndex null
     */
    public function testSingleIndexIsNull()
    {
        $resourceUrlRegex = new ResourceUrlRegex(
            "/MailAccounts\/.+\/MailFolders\/.+\/(MessageItems)(\/*.*$)/m",
            1
        );

        $this->assertNull($resourceUrlRegex->getSingleIndex());

        $this->assertFalse(
            $resourceUrlRegex->isSingleRequest("/MailAccounts/dev/MailFolders/INBOX/MessageItems/1")
        );

        $this->assertFalse(
            $resourceUrlRegex->isSingleRequest("/MailAccounts/dev/MailFolders/INBOX/MessageItems")
        );
    }


    /**
     * Tests toArray()
     *
     * @return void
     */
    public function testToArray()
    {
        $regex = "/MailAccounts\/.+\/MailFolders\/.+\/(MessageItems)(\/*.*$)/m";
        $resourceUrlRegex = new ResourceUrlRegex($regex, 1);

        $this->assertEquals([
            "regex" => $regex, "nameIndex" => 1, "singleIndex" => null
        ], $resourceUrlRegex->toArray());


        $resourceUrlRegex = new ResourceUrlRegex($regex, 1, 2);
        $this->assertEquals([
            "regex" => $regex, "nameIndex" => 1, "singleIndex" => 2
        ], $resourceUrlRegex->toArray());
    }

    /**
     * @return ResourceUrlRegex
     */
    protected function createResourceUrlRegex(): ResourceUrlRegex
    {
        return new ResourceUrlRegex(
            "/MailAccounts\/.+\/MailFolders\/.+\/(MessageItems)(\/*.*$)/m",
            1,
            2
        );
    }
}

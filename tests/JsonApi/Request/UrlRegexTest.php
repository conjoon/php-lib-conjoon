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

use Conjoon\JsonApi\Request\UrlRegex;
use ReflectionException;
use Tests\TestCase;

/**
 * tests UrlRegexList
 */
class UrlRegexTest extends TestCase
{
    /**
     * Tests functionality
     */
    public function testClass()
    {
        $regex = "/MailAccounts\/.+\/MailFolders\/.+\/(MessageItems)(\/*.*$)/m";
        $urlRegex = $this->getMockBuilder(UrlRegex::class)
                         ->enableOriginalConstructor()
                         ->setConstructorArgs([$regex, 1, 2])
                         ->enableProxyingToOriginalMethods()
                         ->onlyMethods(["normalizeName"])
                         ->getMock();

        $this->assertSame($regex, $urlRegex->getRegex());
        $this->assertSame(1, $urlRegex->getNameIndex());
        $this->assertSame(2, $urlRegex->getSingleIndex());

        $urlRegex->expects($this->once())->method("normalizeName")->with("MessageItems");
        $this->assertSame(
            "MessageItem",
            $urlRegex->getResourceName("/MailAccounts/dev/MailFolders/INBOX/MessageItems/1")
        );

        $this->assertTrue(
            $urlRegex->isSingleRequest("/MailAccounts/dev/MailFolders/INBOX/MessageItems/1")
        );

        $this->assertFalse(
            $urlRegex->isSingleRequest("/MailAccounts/dev/MailFolders/INBOX/MessageItems")
        );
    }


    /**
     * Tests normalizeName
     * @throws ReflectionException
     */
    public function testNormalizeName()
    {
        $urlRegex = new UrlRegex("", 1, 2);

        $normalizeName = $this->makeAccessible($urlRegex, "normalizeName");

        $this->assertSame(
            "MessageBody",
            $normalizeName->invokeArgs($urlRegex, ["MessageBodies"])
        );

        $this->assertSame(
            "Address",
            $normalizeName->invokeArgs($urlRegex, ["Addresses"])
        );
    }


    /**
     * @return UrlRegex
     */
    protected function createUrlRegex(): UrlRegex
    {
        return new UrlRegex(
            "/MailAccounts\/.+\/MailFolders\/.+\/(MessageItems)(\/*.*$)/m",
            1,
            2
        );
    }
}

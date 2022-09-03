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
        $ResourceUrlRegex = $this->getMockBuilder(ResourceUrlRegex::class)
                         ->enableOriginalConstructor()
                         ->setConstructorArgs([$regex, 1, 2])
                         ->enableProxyingToOriginalMethods()
                         ->onlyMethods(["normalizeName"])
                         ->getMock();

        $this->assertSame($regex, $ResourceUrlRegex->getRegex());
        $this->assertSame(1, $ResourceUrlRegex->getNameIndex());
        $this->assertSame(2, $ResourceUrlRegex->getSingleIndex());

        $ResourceUrlRegex->expects($this->once())->method("normalizeName")->with("MessageItems");
        $this->assertSame(
            "MessageItem",
            $ResourceUrlRegex->getResourceName("/MailAccounts/dev/MailFolders/INBOX/MessageItems/1")
        );

        $this->assertTrue(
            $ResourceUrlRegex->isSingleRequest("/MailAccounts/dev/MailFolders/INBOX/MessageItems/1")
        );

        $this->assertFalse(
            $ResourceUrlRegex->isSingleRequest("/MailAccounts/dev/MailFolders/INBOX/MessageItems")
        );
    }


    /**
     * Tests normalizeName
     * @throws ReflectionException
     */
    public function testNormalizeName()
    {
        $ResourceUrlRegex = new ResourceUrlRegex("", 1, 2);

        $normalizeName = $this->makeAccessible($ResourceUrlRegex, "normalizeName");

        $this->assertSame(
            "MessageBody",
            $normalizeName->invokeArgs($ResourceUrlRegex, ["MessageBodies"])
        );

        $this->assertSame(
            "Address",
            $normalizeName->invokeArgs($ResourceUrlRegex, ["Addresses"])
        );
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

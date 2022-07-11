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

namespace Tests\Conjoon\JsonApi\Resource;

use Conjoon\Core\Exception\ClassNotFoundException;
use Conjoon\Core\Exception\InvalidTypeException;
use Conjoon\Http\Request\Request;
use Conjoon\JsonApi\Resource\Locator;
use Conjoon\JsonApi\Resource\UrlMatcherLocator;
use Conjoon\JsonApi\Resource\ObjectDescription;
use Tests\TestCase;

/**
 * Tests UrlMatcherLocator
 */
class UrlMatcherLocatorTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $locator = new UrlMatcherLocator("bucket", []);
        $this->assertInstanceOf(Locator::class, $locator);

        $getResourceBucket = $this->makeAccessible($locator, "getResourceBucket");
        $getMatchers = $this->makeAccessible($locator, "getMatchers");

        $this->assertSame("bucket", $getResourceBucket->invokeArgs($locator, []));
        $this->assertSame([], $getMatchers->invokeArgs($locator, []));
    }


    /**
     * tests computeClassName
     */
    public function testComputeClassName()
    {
        $locator = new UrlMatcherLocator("bucket", [
            "/(MailAccounts)\/?[^\/]*$/m",
            "/MailAccounts\/.+\/MailFolders\/.+\/(MessageItems)\/*.*$/m",
            "/MailAccounts\/.+\/(MailFolders)\/?[^\/]*$/m",

        ]);
        $computeClassName = $this->makeAccessible($locator, "computeClassName");

        $tests = [
            "MailAccounts/dev/MailFolders/INBOX/MessageItems/1" => "MessageItem",
            "MailAccounts/dev/MailFolders/INBOX" => "MailFolder",
            "MailAccounts/dev/MailFolders/INBOX/MessageItems" => "MessageItem",
            "MailAccounts/dev" => "MailAccount",
            "MailAccounts" => "MailAccount",
            "4/3/2/1" => null
        ];

        foreach ($tests as $url => $result) {
            $this->assertSame($result, $computeClassName->invokeArgs($locator, [$url]));
        }
    }


    /**
     * tests getResourceTarget with return value null
     */
    public function testGetResourceTargetWithNull()
    {
        $url = "localurl";

        $request = $this->createMockForAbstract(Request::class, ["getUrl"]);
        $request->expects($this->once())->method("getUrl")->willReturn($url);
        $locator = $this->getMockBuilder(UrlMatcherLocator::class)
                        ->disableOriginalConstructor()
                        ->onlyMethods(["computeClassName"])
                        ->getMock();

        $locator->expects($this->once())
                ->method("computeClassName")
                ->with($url)
                ->willReturn(null);

        $this->assertNull($locator->getResourceTarget($request));
    }


    /**
     * tests getResourceTarget with ClassNotFoundException
     */
    public function testGetResourceTargetWithClassNotFoundException()
    {
        $this->expectException(ClassNotFoundException::class);

        $url = "localurl";

        $request = $this->createMockForAbstract(Request::class, ["getUrl"]);
        $request->expects($this->once())->method("getUrl")->willReturn($url);
        $locator = $this->getMockBuilder(UrlMatcherLocator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getResourceBucket", "computeClassName"])
            ->getMock();

        $locator->expects($this->once())
            ->method("getResourceBucket")
            ->willReturn("bucket");

        $locator->expects($this->once())
            ->method("computeClassName")
            ->with($url)
            ->willReturn("someclass");

        $locator->getResourceTarget($request);
    }


    /**
     * tests getResourceTarget with InvalidTypeException
     */
    public function testGetResourceTargetWithInvalidTypeException()
    {
        $this->expectException(InvalidTypeException::class);

        $url = "localurl";

        $request = $this->createMockForAbstract(Request::class, ["getUrl"]);
        $request->expects($this->once())->method("getUrl")->willReturn($url);
        $locator = $this->getMockBuilder(UrlMatcherLocator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getResourceBucket", "computeClassName"])
            ->getMock();

        $locator->expects($this->once())
            ->method("getResourceBucket")
            ->willReturn("Tests\\Conjoon\\JsonApi\\Resource");

        $locator->expects($this->once())
            ->method("computeClassName")
            ->with($url)
            ->willReturn("TestResourceStd");

        $locator->getResourceTarget($request);
    }


    /**
     * tests getResourceTarget()
     */
    public function testGetResourceTarget()
    {
        $this->expectException(InvalidTypeException::class);

        $url = "localurl";

        $request = $this->createMockForAbstract(Request::class, ["getUrl"]);
        $request->expects($this->once())->method("getUrl")->willReturn($url);
        $locator = $this->getMockBuilder(UrlMatcherLocator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getResourceBucket", "computeClassName"])
            ->getMock();

        $locator->expects($this->once())
            ->method("getResourceBucket")
            ->willReturn("Tests\\Conjoon\\JsonApi\\Resource");

        $locator->expects($this->once())
            ->method("computeClassName")
            ->with($url)
            ->willReturn("TestResourceObjectDescription");

        $this->assertInstanceOf(
            ObjectDescription::class,
            $locator->getResourceTarget($request)
        );
    }
}

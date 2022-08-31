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

namespace Tests\Conjoon\JsonApi\Query\Validation;

use Conjoon\Core\Exception\ClassNotFoundException;
use Conjoon\Core\Exception\InvalidTypeException;
use Conjoon\Http\Request\Request;
use Conjoon\JsonApi\Query\Validation\Validator;
use Conjoon\JsonApi\Query\Validation\Locator;
use Conjoon\JsonApi\Query\Validation\UrlMatcherLocator;
use ReflectionException;
use Tests\TestCase;

/**
 * Tests UrlMatcherLocator
 */
class UrlMatcherLocatorTest extends TestCase
{
    /**
     * Class functionality
     * @throws ReflectionException
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
     * @throws ReflectionException
     */
    public function testComputeClassName()
    {
        $locator = new UrlMatcherLocator("bucket", [
            "/(MailAccounts)(\/)?[^\/]*$/m",
            "/MailAccounts\/.+\/MailFolders\/.+\/(MessageBodies)(\/)*.*$/m",
            "/MailAccounts\/.+\/MailFolders\/.+\/(MessageItems)(\/)*.*$/m",
            "/MailAccounts\/.+\/(MailFolders)(\/)?[^\/]*$/m",

        ]);
        $computeClassName = $this->makeAccessible($locator, "computeClassName");

        $tests = [
            "MailAccounts/dev/MailFolders/INBOX/MessageBodies/1" => "MessageBodyValidator",
            "MailAccounts/dev/MailFolders/INBOX/MessageBodies" => "MessageBodyCollectionValidator",
            "MailAccounts/dev/MailFolders/INBOX/MessageItems/1" => "MessageItemValidator",
            "MailAccounts/dev/MailFolders/INBOX" => "MailFolderValidator",
            "MailAccounts/dev/MailFolders/INBOX/MessageItems" => "MessageItemCollectionValidator",
            "MailAccounts/dev" => "MailAccountValidator",
            "MailAccounts" => "MailAccountCollectionValidator",
            "4/3/2/1" => null
        ];

        foreach ($tests as $url => $result) {
            $this->assertSame($result, $computeClassName->invokeArgs($locator, [$url]));
        }
    }


    /**
     * tests getQueryValidator with return value null
     */
    public function testGetQueryValidatorWithNull()
    {
        $url = "localUrl";

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

        $this->assertNull($locator->getQueryValidator($request));
    }


    /**
     * tests getQueryValidator with ClassNotFoundException
     */
    public function testGetQueryValidatorWithClassNotFoundException()
    {
        $this->expectException(ClassNotFoundException::class);

        $url = "localUrl";

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
            ->willReturn("someClass");

        $locator->getQueryValidator($request);
    }


    /**
     * tests getQueryValidator with InvalidTypeException
     */
    public function testGetQueryValidatorWithInvalidTypeException()
    {
        $this->expectException(InvalidTypeException::class);

        $url = "localUrl";

        $request = $this->createMockForAbstract(Request::class, ["getUrl"]);
        $request->expects($this->once())->method("getUrl")->willReturn($url);
        $locator = $this->getMockBuilder(UrlMatcherLocator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getResourceBucket", "computeClassName"])
            ->getMock();

        $locator->expects($this->once())
            ->method("getResourceBucket")
            ->willReturn("Tests\\Conjoon\\JsonApi\\Query\\Validation");

        $locator->expects($this->once())
            ->method("computeClassName")
            ->with($url)
            ->willReturn("TestValidatorStd");

        $locator->getQueryValidator($request);
    }


    /**
     * tests getQueryValidator()
     */
    public function testGetQueryValidator()
    {
        $url = "localUrl";

        $request = $this->createMockForAbstract(Request::class, ["getUrl"]);
        $request->expects($this->once())->method("getUrl")->willReturn($url);
        $locator = $this->getMockBuilder(UrlMatcherLocator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(["getResourceBucket", "computeClassName"])
            ->getMock();

        $locator->expects($this->once())
            ->method("getResourceBucket")
            ->willReturn("Tests\\Conjoon\\JsonApi\\Query\\Validation");

        $locator->expects($this->once())
            ->method("computeClassName")
            ->with($url)
            ->willReturn("TestQueryValidator");

        $this->assertInstanceOf(
            Validator::class,
            $locator->getQueryValidator($request)
        );
    }
}

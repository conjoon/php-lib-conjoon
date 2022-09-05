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
use Conjoon\JsonApi\Request\ResourceUrlParser;
use Conjoon\JsonApi\Request\ResourceUrlRegex;
use Conjoon\JsonApi\Request\ResourceUrlRegexList;
use Conjoon\JsonApi\Resource\Locator;
use Conjoon\JsonApi\Resource\UrlMatcherLocator;
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
        $parser = $this->createParser("");
        $locator = new UrlMatcherLocator($parser);
        $this->assertInstanceOf(Locator::class, $locator);

        $this->assertSame($parser, $locator->getResourceUrlParser());
    }


    /**
     * tests getResourceTarget with return value null
     */
    public function testGetResourceTargetWithNull()
    {
        $url = "localurl";

        $request = $this->createMockForAbstract(Request::class, ["getUrl"]);
        $request->expects($this->once())->method("getUrl")->willReturn($url);

        $parser = $this->createParser("");
        $locator = new UrlMatcherLocator($parser);

        $this->assertNull($locator->getResourceTarget($request));
    }


    /**
     * tests getResourceTarget with ClassNotFoundException
     */
    public function testGetResourceTargetWithClassNotFoundException()
    {
        $this->expectException(ClassNotFoundException::class);

        $url     = "MailAccounts/dev/MailFolders/INBOX/MessageItems";
        $parser  = $this->createParser("notfound");
        $locator = new UrlMatcherLocator($parser);

        $request = $this->createMockForAbstract(Request::class, ["getUrl"]);
        $request->expects($this->once())->method("getUrl")->willReturn($url);

        $locator->getResourceTarget($request);
    }


    /**
     * tests getResourceTarget with InvalidTypeException
     */
    public function testGetResourceTargetWithInvalidTypeException()
    {
        $this->expectException(InvalidTypeException::class);

        $url     = "MailAccounts/dev/MailFolders/INBOX/MessageItems";
        $parser  = $this->createParser("Tests\\Conjoon\\JsonApi\\Resource\\TestResourceStd");
        $locator = new UrlMatcherLocator($parser);

        $request = $this->createMockForAbstract(Request::class, ["getUrl"]);
        $request->expects($this->once())->method("getUrl")->willReturn($url);

        $locator->getResourceTarget($request);
    }


    /**
     * tests getResourceTarget()
     */
    public function testGetResourceTarget()
    {
        $url     = "MailAccounts/dev/MailFolders/INBOX/MessageItems";
        $parser  = $this->createParser("Tests\\Conjoon\\JsonApi\\Resource\\TestResourceObjectDescription");
        $locator = new UrlMatcherLocator($parser);

        $request = $this->createMockForAbstract(Request::class, ["getUrl"]);
        $request->expects($this->once())->method("getUrl")->willReturn($url);

        $locator->getResourceTarget($request);
    }


    /**
     * @param $template
     * @return ResourceUrlParser
     */
    protected function createParser($template): ResourceUrlParser
    {
        $urlRegexList = new ResourceUrlRegexList();
        $urlRegexList[] = new ResourceUrlRegex("/(MailAccounts)(\/)?[^\/]*$/m", 1, 2);
        $urlRegexList[] = new ResourceUrlRegex("/MailAccounts\/.+\/MailFolders\/.+\/(MessageItems)(\/*.*$)/m", 1, 2);
        $urlRegexList[] = new ResourceUrlRegex("/MailAccounts\/.+\/(MailFolders)(\/)?[^\/]*$/m", 1, 2);

        return new ResourceUrlParser($urlRegexList, $template);
    }
}

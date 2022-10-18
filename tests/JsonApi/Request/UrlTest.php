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

use Conjoon\Data\Resource\ObjectDescription;
use Conjoon\Http\Url;
use Conjoon\JsonApi\Request\Url as JsonApiUrl;
use Tests\Conjoon\Http\UrlTest as HttpUrlTest;

/**
 * Tests JsonApiUrl.
 */
class UrlTest extends HttpUrlTest
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $urlString = "http://www.localhost.com:8080/index.php?foo=bar";
        $url = $this->createTestInstance($urlString);
        $this->assertInstanceOf(Url::class, $url);
    }


    /**
     * Tests getResourceTarget()
     * @return void
     */
    public function testGetResourceTarget(): void
    {
        $resourceTarget = $this->createMockForAbstract(ObjectDescription::class);
        $url = new JsonApiUrl("", $resourceTarget);

        $this->assertSame($resourceTarget, $url->getResourceTarget());
    }


    /**
     * @param string $urlString
     * @return Url
     */
    protected function createTestInstance(string $urlString): Url
    {
        $resourceTarget = $this->createMockForAbstract(ObjectDescription::class);
        return new JsonApiUrl($urlString, $resourceTarget);
    }


    /**
     * @return string
     */
    protected function getTestedClass(): string
    {
        return JsonApiUrl::class;
    }
}

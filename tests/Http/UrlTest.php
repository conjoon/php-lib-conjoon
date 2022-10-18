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

namespace Tests\Conjoon\Http;

use Conjoon\Core\Contract\Stringable;
use Conjoon\Http\Url;
use Tests\StringableTestTrait;
use Tests\TestCase;

/**
 * Tests LaravelUrl.
 */
class UrlTest extends TestCase
{
    use StringableTestTrait;

    /**
     * Class functionality
     */
    public function testClass()
    {
        $urlString = "http://www.localhost.com:8080/index.php?foo=bar";
        $url = $this->createTestInstance($urlString);
        $this->assertInstanceOf(Stringable::class, $url);
    }


    /**
     * Tests getQuery()
     * @return void
     */
    public function testGetQuery()
    {
        $urlString = "http://www.localhost.com:8080/index.php?foo=bar";
        $url = $this->createTestInstance($urlString);
        $query = $url->getQuery();

        $this->assertNotNull($query);
        $this->assertSame("foo=bar", $query->toString());
        // always yields same result
        $this->assertSame($query, $url->getQuery());

        // null query
        $urlString = "http://www.localhost.com:8080/index.php?";
        $url = $this->createTestInstance($urlString);
        // always yields same result
        $this->assertNull($url->getQuery());
        $this->assertNull($url->getQuery());
    }


    /**
     * Tests toString()
     * @return void
     */
    public function testToString(): void
    {
        $urlString = "http://www.localhost.com:8080/index.php?foo=bar";
        $url = $this->createTestInstance($urlString);
        $this->assertSame($urlString, $url->toString());
        $this->runToStringTest($this->getTestedClass());
    }


    /**
     * @param string $urlString
     * @return Url
     */
    protected function createTestInstance(string $urlString): Url
    {
        return new Url($urlString);
    }


    /**
     * @return string
     */
    protected function getTestedClass(): string
    {
        return Url::class;
    }
}

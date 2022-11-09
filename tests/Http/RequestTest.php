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

use Conjoon\Http\Request;
use Conjoon\Http\RequestMethod;
use Conjoon\Net\Url;
use Tests\TestCase;

/**
 * Tests Request..
 */
class RequestTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass(): void
    {
        $url = new Url("https://www.localhost.com:8080/index.php");
        $request = new Request($url);

        $this->assertSame($url, $request->getUrl());
        $this->assertSame(RequestMethod::GET, $request->getMethod());

        $request = new Request($url, RequestMethod::POST);
        $this->assertSame(RequestMethod::POST, $request->getMethod());
    }


    public function testGetQuery(): void
    {
        $request = new Request(Url::create("https://www.localhost.com:8080/index.php"));
        $this->assertNull($request->getQuery());

        $request = new Request(Url::create("https://www.localhost.com:8080/index.php?query=value"));
        $query = $request->getQuery();
        $this->assertNotNull($query);
        $this->assertSame(
            ["query" => "value"],
            $query->toArray()
        );
    }
}

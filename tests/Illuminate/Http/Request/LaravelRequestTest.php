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

namespace Tests\Conjoon\Illuminate\Http\Request;

use Conjoon\Http\Request\Request;
use Conjoon\Illuminate\Http\LaravelUrl;
use Conjoon\Illuminate\Http\Request\LaravelRequest;
use Tests\TestCase;
use Illuminate\Http\Request as IlluminateRequest;

/**
 * Tests LaravelQuery.
 */
class LaravelRequestTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $url = "http://www.localhost.com:8080/index.php";
        $queryString = "query=string";

        $request = new LaravelRequest(
            IlluminateRequest::create("$url?$queryString")
        );

        $this->assertInstanceOf(Request::class, $request);

        $this->assertSame("GET", $request->getMethod());

        $this->assertInstanceOf(LaravelUrl::class, $request->getUrl());
        $this->assertSame($queryString, $request->getUrl()->getQuery()->toString());
        $this->assertSame("string", $request->getUrl()->getQuery()->getParameter("query")->getValue());
    }
}

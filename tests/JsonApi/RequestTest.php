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

use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\JsonApi\Query\Query as JsonApiQuery;
use Conjoon\Net\Url;
use Conjoon\Http\RequestMethod as Method;
use Conjoon\JsonApi\Query\Validation\Validator;
use Conjoon\JsonApi\Request as JsonApiRequest;
use Conjoon\Http\Request as HttpRequest;
use Conjoon\Data\Resource\ObjectDescription;
use Tests\TestCase;

/**
 * Tests Request
 */
class RequestTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $validator = $this->createMockForAbstract(Validator::class);
        $url = new Url("http://www.localhost.com:8080/index.php");
        $request = new JsonApiRequest(
            url: $url,
            queryValidator: $validator
        );

        $this->assertSame(Method::GET, $request->getMethod());
        $this->assertSame($url, $request->getUrl());

        $getQueryValidator = $this->makeAccessible($request, "getQueryValidator");
        $this->assertSame($validator, $getQueryValidator->invokeArgs($request, []));

        $this->assertInstanceOf(HttpRequest::class, $request);

        $request = new JsonApiRequest($url, method: Method::PATCH);
        $getQueryValidator = $this->makeAccessible($request, "getQueryValidator");
        $this->assertNull($getQueryValidator->invokeArgs($request, []));
        $this->assertSame(Method::PATCH, $request->getMethod());
    }


    /**
     * tests validate()
     */
    public function testValidate()
    {

        $query = $this->getMockBuilder(JsonApiQuery::class)->disableOriginalConstructor()->getMock();

        $url = $this->getMockBuilder(Url::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $validator = $this->createMockForAbstract(Validator::class, ["validate"]);

        $validator->expects($this->once())->method("validate")->with($query, $this->callback(function ($arg) {
            $this->assertInstanceOf(
                ValidationErrors::class,
                $arg
            );
            return true;
        }));

        $request = $this->getMockBuilder(JsonApiRequest::class)
                        ->setConstructorArgs([$url, Method::GET, $validator])
                        ->onlyMethods(["getQuery"])
                        ->getMock();
        $request->expects($this->once())->method("getQuery")->willReturn($query);

        $request->validate();
    }
}

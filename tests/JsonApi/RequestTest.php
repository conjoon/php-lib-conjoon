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
use Conjoon\Http\RequestMethod;
use Conjoon\JsonApi\Query\Query as JsonApiQuery;
use Conjoon\JsonApi\Request\Url as JsonApiUrl;
use Conjoon\JsonApi\Query\Validation\Validator;
use Conjoon\JsonApi\Request\Request as JsonApiRequest;
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
        $resourceTarget = $this->createMockForAbstract(ObjectDescription::class);
        $validator = $this->createMockForAbstract(Validator::class);
        $url = new JsonApiUrl("http://www.localhost.com:8080/index.php", $resourceTarget, true);
        $request = new JsonApiRequest(
            url: $url,
            queryValidator: $validator
        );

        $this->assertSame(Method::GET, $request->getMethod());
        $this->assertSame($url, $request->getUrl());
        $this->assertSame($validator, $request->getQueryValidator());
        $this->assertSame($resourceTarget, $request->getResourceTarget());
        $this->assertTrue($request->targetsResourceCollection());

        $this->assertInstanceOf(HttpRequest::class, $request);

        $request = new JsonApiRequest($url, method: Method::PATCH);
        $this->assertNull($request->getQueryValidator());
        $this->assertSame(Method::PATCH, $request->getMethod());
    }


    /**
     * tests validate()
     */
    public function testValidate()
    {
        $resourceTarget = $this->createMockForAbstract(ObjectDescription::class);

        $query = $this->getMockBuilder(JsonApiQuery::class)->disableOriginalConstructor()->getMock();

        $url = $this->getMockBuilder(JsonApiUrl::class)
                    ->disableOriginalConstructor()
                    ->onlyMethods(["getResourceTarget", "getQuery"])
                    ->getMock();
        $url->expects($this->any())->method("getResourceTarget")->willReturn($resourceTarget);
        $url->expects($this->any())->method("getQuery")->willReturn($query);

        $validator = $this->createMockForAbstract(Validator::class, ["validate"]);

        $validator->expects($this->once())->method("validate")->with($query, $this->callback(function ($arg) {
            $this->assertInstanceOf(
                ValidationErrors::class,
                $arg
            );
            return true;
        }));

        $request = new JsonApiRequest(url: $url, queryValidator: $validator);
        $errors = $request->validate();

        $this->assertInstanceOf(
            ValidationErrors::class,
            $errors
        );
    }
}

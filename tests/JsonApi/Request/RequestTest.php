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
use Conjoon\Http\Query\Query as HttpQuery;
use Conjoon\JsonApi\Query\Query;
use Conjoon\JsonApi\Query\Validation\Validator;
use Conjoon\JsonApi\Request\Request;
use Conjoon\Http\Request\Request as HttpRequest;
use Conjoon\Data\Resource\ObjectDescription;
use PHPUnit\Framework\MockObject\MockObject;
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
        $resourceTarget = $this->createResourceTarget();
        $decoratedRequest = $this->createRequest(["getMethod", "getUrl", "getQuery"]);

        $method = "GET";
        $url = "http://localhost:8080";

        $decoratedRequest->expects($this->once())->method("getMethod")->willReturn($method);
        $decoratedRequest->expects($this->once())->method("getUrl")->willReturn($url);

        $request = new Request($decoratedRequest, $resourceTarget);

        $this->assertNull($request->getQueryValidator());
        $this->assertSame($method, $request->getMethod());
        $this->assertSame($url, $request->getUrl());

        $this->assertInstanceOf(HttpRequest::class, $request);
        $this->assertSame($resourceTarget, $request->getResourceTarget());


        $validator = $this->getMockForAbstractClass(Validator::class);
        $request = new Request($decoratedRequest, $resourceTarget, $validator);

        $this->assertSame($validator, $request->getQueryValidator());
    }


    /**
     * tests validate() without available Validator
     */
    public function testValidateWithoutValidator()
    {
        $resourceTarget = $this->createResourceTarget();
        $decoratedRequest = $this->createRequest();

        $request = new Request($decoratedRequest, $resourceTarget);

        $errors = $request->validate();

        $this->assertInstanceOf(
            ValidationErrors::class,
            $errors
        );

        $this->assertFalse($errors->hasError());
    }


    /**
     * tests validate()
     */
    public function testValidate()
    {
        $query = $this->getMockBuilder(Query::class)->disableOriginalConstructor()->getMock();

        $resourceTarget   = $this->createResourceTarget();
        $decoratedRequest = $this->createRequest();

        $validator = $this->createMockForAbstract(Validator::class, ["validate"]);

        $validator->expects($this->once())->method("validate")->with($query, $this->callback(function ($arg) {
            $this->assertInstanceOf(
                ValidationErrors::class,
                $arg
            );
            return true;
        }));

        $request = $this->getMockBuilder(Request::class)->enableOriginalConstructor()->setConstructorArgs(
            [$decoratedRequest, $resourceTarget, $validator]
        )->onlyMethods(["getQuery"])->getMock();

        $request->expects($this->once())->method("getQuery")->willReturn($query);

        $errors = $request->validate();

        $this->assertInstanceOf(
            ValidationErrors::class,
            $errors
        );
    }


    /**
     * tests getQuery()
     */
    public function testGetQuery()
    {
        $query = $this->createQuery();
        $resourceTarget = $this->createResourceTarget();

        $decoratedRequest = $this->createRequest(["getQuery"]);
        $decoratedRequest->expects($this->any())->method("getQuery")->willReturn($query);

        $request = new Request($decoratedRequest, $resourceTarget);

        $jsonApiQuery = $request->getQuery();
        $this->assertInstanceOf(Query::class, $jsonApiQuery);
        $this->assertSame($jsonApiQuery, $request->getQuery());
        $this->assertSame($resourceTarget, $jsonApiQuery->getResourceTarget());


        // null
        $decoratedRequest = $this->createRequest(["getQuery"]);
        $decoratedRequest->expects($this->any())->method("getQuery")->willReturn(null);

        $request = new Request($decoratedRequest, $resourceTarget);

        $jsonApiQuery = $request->getQuery();
        $this->assertNull($jsonApiQuery);
        $this->assertSame($jsonApiQuery, $request->getQuery());
    }


    /**
     * @return MockObject
     */
    protected function createQuery(): MockObject
    {
        return $this->createMockForAbstract(HttpQuery::class);
    }


    /**
     * @return MockObject
     */
    protected function createResourceTarget(): MockObject
    {
        return $this->createMockForAbstract(ObjectDescription::class);
    }


    /**
     * @param array $methods
     * @return MockObject
     */
    protected function createRequest(array $methods = []): MockObject
    {
        return $this->createMockForAbstract(HttpRequest::class, $methods);
    }
}

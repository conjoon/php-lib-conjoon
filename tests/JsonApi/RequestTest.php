<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2022-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */


declare(strict_types=1);

namespace Tests\Conjoon\JsonApi\Request;

use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Http\RequestMethod;
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
        $objectDescription = $this->createMockForAbstract(ObjectDescription::class);
        $url = new Url("http://www.localhost.com:8080/index.php?foo=bar");
        $request = new JsonApiRequest(
            url: $url,
            method: RequestMethod::GET,
            objectDescription: $objectDescription,
            queryValidator: $validator
        );

        $this->assertSame(Method::GET, $request->getMethod());
        $this->assertSame($url, $request->getUrl());

        $this->assertInstanceOf(JsonApiQuery::class, $request->getQuery());

        $getQueryValidator = $this->makeAccessible($request, "getQueryValidator");
        $this->assertSame($validator, $getQueryValidator->invokeArgs($request, []));

        $this->assertInstanceOf(HttpRequest::class, $request);

        $request = new JsonApiRequest(url: $url, method: Method::PATCH, objectDescription: $objectDescription);
        $getQueryValidator = $this->makeAccessible($request, "getQueryValidator");
        $this->assertNull($getQueryValidator->invokeArgs($request, []));
        $this->assertSame(Method::PATCH, $request->getMethod());
    }


    /**
     * tests validate()
     */
    public function testValidate()
    {
        $objectDescription = $this->createMockForAbstract(ObjectDescription::class);

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
                        ->setConstructorArgs([$url, Method::GET, $objectDescription, $validator])
                        ->onlyMethods(["getQuery"])
                        ->getMock();
        $request->expects($this->once())->method("getQuery")->willReturn($query);

        $request->validate();
    }
}

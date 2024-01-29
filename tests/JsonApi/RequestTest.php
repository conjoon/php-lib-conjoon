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

use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Http\Request as HttpRequest;
use Conjoon\Http\RequestMethod;
use Conjoon\Http\RequestMethod as Method;
use Conjoon\JsonApi\JsonApiRequest;
use Conjoon\JsonApi\PathMatcher;
use Conjoon\JsonApi\PathMatcherResult;
use Conjoon\JsonApi\Query\JsonApiQuery as JsonApiQuery;
use Conjoon\JsonApi\Query\Validation\JsonApiQueryValidator;
use Conjoon\Net\Url;
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
        $pathMatcher = $this->createMockForAbstract(PathMatcher::class);
        $validator = $this->createMockForAbstract(JsonApiQueryValidator::class);
        $resourceDescription = $this->createMockForAbstract(ResourceDescription::class);
        $url = new Url("http://www.localhost.com:8080/index.php?foo=bar");
        $request = new JsonApiRequest(
            url: $url,
            method: RequestMethod::GET,
            pathMatcher: $pathMatcher,
        );

        $pathMatcher->expects($this->any())->method("match")->with($url)->willReturn(
            $this->getPathMatcherResult($resourceDescription, $validator)
        );

        $this->assertSame(Method::GET, $request->getMethod());
        $this->assertSame($url, $request->getUrl());

        $this->assertInstanceOf(JsonApiQuery::class, $request->getQuery());

        $getQueryValidator = $this->makeAccessible($request, "getQueryValidator");
        $this->assertSame($validator, $getQueryValidator->invokeArgs($request, []));

        $this->assertInstanceOf(HttpRequest::class, $request);

        // no query
        $url = new Url("http://www.localhost.com:8080/index.php");
        $pathMatcher = $this->createMockForAbstract(PathMatcher::class);
        $request = new JsonApiRequest(url: $url, method: Method::PATCH, pathMatcher: $pathMatcher);
        $getQueryValidator = $this->makeAccessible($request, "getQueryValidator");
        $this->assertNull($getQueryValidator->invokeArgs($request, []));
        $this->assertSame(Method::PATCH, $request->getMethod());

        $this->assertSame(0, $request->validate()->count());
    }


    /**
     * tests validate()
     */
    public function testValidate()
    {
        $pathMatcher = $this->createMockForAbstract(PathMatcher::class);
        $url = new Url("http://www.localhost.com:8080/index.php?foo=bar");
        $resourceDescription = $this->createMockForAbstract(ResourceDescription::class);
        $validator = $this->createMockForAbstract(JsonApiQueryValidator::class, ["validate"]);
        $pathMatcher->expects($this->any())->method("match")->with($url)->willReturn(
            $this->getPathMatcherResult($resourceDescription, $validator)
        );
        $query = $this->getMockBuilder(JsonApiQuery::class)->disableOriginalConstructor()->getMock();

        $validator->expects($this->once())->method("validate")->with($query, $this->callback(function ($arg) {
            $this->assertInstanceOf(
                ValidationErrors::class,
                $arg
            );
            return true;
        }));

        $request = $this->getMockBuilder(JsonApiRequest::class)
                        ->setConstructorArgs([$url, Method::GET, $pathMatcher])
                        ->onlyMethods(["getQuery"])
                        ->getMock();
        $request->expects($this->once())->method("getQuery")->willReturn($query);

        $request->validate();
    }

    protected function getPathMatcherResult(
        ResourceDescription $description,
        ?JsonApiQueryValidator $validator
    ): PathMatcherResult {

        $pathMatcherResult = $this->createMockForAbstract(PathMatcherResult::class);

        $pathMatcherResult->expects($this->any())->method("getResourceDescription")->willReturn(
            $description
        );

        $pathMatcherResult->expects($this->any())->method("getQueryValidator")->willReturn(
            $validator
        );

        return $pathMatcherResult;
    }

}

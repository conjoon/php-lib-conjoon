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
use Conjoon\Http\RequestMethod as Method;
use Conjoon\JsonApi\Request;
use Conjoon\JsonApi\Query\JsonApiQuery;
use Conjoon\JsonApi\Query\Validation\CollectionQueryValidator;
use Conjoon\JsonApi\Query\Validation\JsonApiQueryValidator;
use Conjoon\Net\Uri\Component\Path\ParameterList;
use Conjoon\Net\Url;
use Tests\TestCase;


class RequestTest extends TestCase
{
    public function testClass()
    {
        $url = Url::make("http://localhost:8080/uri");
        $validator = new JsonApiQueryValidator();
        $jsonApiRequest = $this->makeJsonApiRequest($url, $validator);

        $this->assertNull($jsonApiRequest->getQuery());

        $this->assertSame($validator, $jsonApiRequest->getQueryValidator());
        $this->assertFalse($jsonApiRequest->targetsCollection());
        $this->assertSame(0, $jsonApiRequest->validate()->count());
    }

    public function testWithQuery()
    {
        $url = Url::make("http://localhost:8080/uri?query=true");
        $validator = new JsonApiQueryValidator();
        $jsonApiRequest = $this->makeJsonApiRequest($url, $validator);

        $this->assertInstanceOf(JsonApiQuery::class, $jsonApiRequest->getQuery());
        $this->assertSame("query=true", (string) $jsonApiRequest->getQuery());

        $this->assertSame($validator, $jsonApiRequest->getQueryValidator());
        $this->assertFalse($jsonApiRequest->targetsCollection());
        // Error: found additional parameters "query"
        $this->assertInstanceOf(ValidationErrors::class, $jsonApiRequest->validate());
    }

    public function testTargetsCollection()
    {
        $url = Url::make("http://localhost:8080/uri?query=true");
        $validator = new CollectionQueryValidator();
        $jsonApiRequest = $this->makeJsonApiRequest($url, $validator);

        $this->assertTrue($jsonApiRequest->targetsCollection());
    }

    protected function makeJsonApiRequest(Url $url, JsonApiQueryValidator $validator): Request {
        $resourceDescription = $this->createMockForAbstract(ResourceDescription::class);
        $request = new HttpRequest($url, Method::GET);

        $pathParameters = new ParameterList();

        $jsonApiRequest = new Request(
            $request, $pathParameters, $resourceDescription, $validator
        );
        $this->assertTrue($url->equals($jsonApiRequest->getUrl()));
        $this->assertTrue($request->getMethod() === $jsonApiRequest->getMethod());

        $this->assertSame($resourceDescription, $jsonApiRequest->getResourceDescription());
        $this->assertSame($pathParameters, $jsonApiRequest->getPathParameters());

        return $jsonApiRequest;
    }

}

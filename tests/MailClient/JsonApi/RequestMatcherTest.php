<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Tests\Conjoon\MailClient\JsonApi;

use Conjoon\Http\Exception\NotFoundException;
use Conjoon\Http\Request;
use Conjoon\JsonApi\Query\Validation\QueryValidator;
use Conjoon\MailClient\Data\Resource\MailAccountDescription;
use Conjoon\MailClient\Data\Resource\MailFolderDescription;
use Conjoon\MailClient\JsonApi\RequestMatcher;
use Conjoon\JsonApi\RequestMatcher as JsonApiRequestMatcher;
use Conjoon\Net\Url;
use Tests\TestCase;

class RequestMatcherTest extends TestCase
{
    public function testClass(): void
    {
        $this->assertInstanceOf(JsonApiRequestMatcher::class, new RequestMatcher());
    }


    public function testNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $matcher = new RequestMatcher();
        $url = Url::make("https://localhost:8080/rest-api/MailAccounts/3?query=value");
        $request = new Request($url);
        $this->assertNull($matcher->match($request));
    }


    public function testMailAccountMatch(): void
    {
        $matcher = new RequestMatcher();

        $url = Url::make("https://localhost:8080/rest-api/MailAccounts?query=value");
        $request = new Request($url);
        $jsonApiRequest  = $matcher->match($request);

        $this->assertNotNull($jsonApiRequest);
        $this->assertInstanceOf(MailAccountDescription::class, $jsonApiRequest->getResourceDescription());
        $this->assertInstanceOf(QueryValidator::class, $jsonApiRequest->getQueryValidator());
        $this->assertFalse($jsonApiRequest->targetsCollection());

        $this->assertEquals([], $jsonApiRequest->getPathParameters()->toArray());
    }


    public function testMailFolderMatch(): void
    {
        $matcher = new RequestMatcher();

        $url = Url::make("https://localhost:8080/rest-api/MailAccounts/1/MailFolders?query=value");
        $request = new Request($url);
        $jsonApiRequest  = $matcher->match($request);

        $this->assertNotNull($jsonApiRequest);
        $this->assertInstanceOf(MailFolderDescription::class, $jsonApiRequest->getResourceDescription());
        $this->assertInstanceOf(QueryValidator::class, $jsonApiRequest->getQueryValidator());
        $this->assertFalse($jsonApiRequest->targetsCollection());

        $this->assertEquals(["mailAccountId" => "1"], $jsonApiRequest->getPathParameters()->toArray());
    }
}

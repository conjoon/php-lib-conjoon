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

use Conjoon\Http\Exception\BadRequestException;
use Conjoon\Http\Exception\NotFoundException;
use Conjoon\Http\Header;
use Conjoon\Http\HeaderList;
use Conjoon\Http\Request;
use Conjoon\Http\RequestMethod;
use Conjoon\MailClient\Data\Resource\MailAccountDescription;
use Conjoon\MailClient\Data\Resource\MailFolderDescription;
use Conjoon\MailClient\Data\Resource\MessageItemDescription;
use Conjoon\MailClient\JsonApi\Query\MailAccountListQueryValidator;
use Conjoon\MailClient\JsonApi\Query\MailFolderListQueryValidator;
use Conjoon\MailClient\JsonApi\Query\MessageItemListQueryValidator;
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

    public function testBadRequestExceptionDueToMissingHeader(): void
    {
        $this->expectException(BadRequestException::class);
        $matcher = new RequestMatcher();
        $url = Url::make("https://localhost:8080/rest-api/MailAccounts/3?query=value");
        $request = new Request($url);
        $this->assertNull($matcher->match($request));
    }

    public function testNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $matcher = new RequestMatcher();
        $request = $this->makeRequest("https://localhost:8080/rest-api/MailAccounts/3?query=value");
        $this->assertNull($matcher->match($request));
    }


    public function testMailAccountMatch(): void
    {
        $matcher = new RequestMatcher();

        $request = $this->makeRequest("https://localhost:8080/rest-api/MailAccounts?query=value");
        $jsonApiRequest  = $matcher->match($request);

        $this->assertNotNull($jsonApiRequest);
        $this->assertInstanceOf(MailAccountDescription::class, $jsonApiRequest->getResourceDescription());
        $this->assertInstanceOf(MailAccountListQueryValidator::class, $jsonApiRequest->getQueryValidator());
        $this->assertTrue($jsonApiRequest->targetsCollection());

        $this->assertEquals([], $jsonApiRequest->getPathParameters()->toArray());
    }


    public function testMailFolderMatch(): void
    {
        $matcher = new RequestMatcher();

        $request = $this->makeRequest("https://localhost:8080/rest-api/MailAccounts/1/MailFolders?query=value");
        $jsonApiRequest  = $matcher->match($request);

        $this->assertNotNull($jsonApiRequest);
        $this->assertInstanceOf(MailFolderDescription::class, $jsonApiRequest->getResourceDescription());
        $this->assertInstanceOf(MailFolderListQueryValidator::class, $jsonApiRequest->getQueryValidator());
        $this->assertTrue($jsonApiRequest->targetsCollection());

        $this->assertEquals(["mailAccountId" => "1"], $jsonApiRequest->getPathParameters()->toArray());
    }


    public function testMessageItemsMatch(): void
    {
        $matcher = new RequestMatcher();

        $request = $this->makeRequest("https://localhost:8080/rest-api/MailAccounts/1/MailFolders/2/MessageItems?query=value");
        $jsonApiRequest  = $matcher->match($request);

        $this->assertNotNull($jsonApiRequest);
        $this->assertInstanceOf(MessageItemDescription::class, $jsonApiRequest->getResourceDescription());
        $this->assertInstanceOf(MessageItemListQueryValidator::class, $jsonApiRequest->getQueryValidator());
        $this->assertTrue($jsonApiRequest->targetsCollection());

        $this->assertEquals(["mailAccountId" => "1", "mailFolderId" => 2], $jsonApiRequest->getPathParameters()->toArray());
    }


    private function makeRequest(string $url) {

        $url = Url::make($url);
        $headerList = HeaderList::make(Header::make(
            "Accept",
            "application/vnd.api+json;ext=\"https://conjoon.org/json-api/ext/relfield\""
        ));
        return new Request($url, RequestMethod::GET, $headerList);


    }
}

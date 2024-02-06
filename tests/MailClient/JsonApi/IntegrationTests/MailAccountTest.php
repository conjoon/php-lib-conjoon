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

namespace Tests\Conjoon\MailClient\JsonApi\IntegrationTests;

use Conjoon\Http\Exception\NotFoundException;
use Conjoon\Illuminate\Auth\ImapUser;
use Conjoon\JsonApi\Exception\BadRequestException;
use Conjoon\JsonApi\Resource\Resource;
use Conjoon\MailClient\JsonApi\ResourceResolver as MailClientResourceResolver;
use Conjoon\MailClient\Data\MailAccount;
use Tests\TestCase;
use Tests\TestTrait;

class MailAccountTest extends TestCase
{
    use IntegrationTestTrait;
    use TestTrait;

    /**
     * Test NotFoundException.
     *
     * @return void
     */
    public function testGetMailAccountNotFoundException()
    {
        $this->expectException(NotFoundException::class);
        $this->buildJsonApiRequest("https://localhost:8080/rest-api/v1/MaisaflAccounts");
    }

    /**
     * Test BadRequestException.
     *
     * @return void
     */
    public function testGetMailAccountBadRequestException()
    {
        $this->expectException(BadRequestException::class);
        $jsonApiRequest = $this->buildJsonApiRequest("https://localhost:8080/rest-api/v1/MailAccounts?include=me");
        $this->assertNotNull($jsonApiRequest);

        $resourceResolver = $this->getResourceResolver();
        $resourceResolver->resolve($jsonApiRequest);
    }

    /**
     * Test getMailAccount.
     *
     * @return void
     */
    public function testGetMailAccount()
    {
        $request = $this->buildJsonApiRequest("https://localhost:8080/rest-api/v1/MailAccounts");
        $this->assertNotNull($request);

        $resourceResolver = $this->getResourceResolver();
        $resource = $resourceResolver->resolve($request);

        $this->assertEquals(
            $this->getIntegrationTestUser()->getMailAccounts()->toJson($this->getJsonApiStrategy()),
            $resource->toJson($this->getJsonApiStrategy())
        );
    }

}

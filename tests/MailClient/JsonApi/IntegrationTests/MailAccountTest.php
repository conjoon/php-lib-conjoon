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
use Conjoon\JsonApi\Exception\BadRequestException;
use Conjoon\JsonApi\Resource\Resource;
use Conjoon\JsonApi\Resource\ResourceResolver;
use Conjoon\MailClient\Data\MailAccount;
use Tests\TestCase;

class MailAccountTest extends TestCase
{
    use IntegrationTestTrait;

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
        $resourceResolver = $this->createMockForAbstract(ResourceResolver::class);

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

        $resourceResolver = $this->createMockForAbstract(ResourceResolver::class);
        $resourceResolver->expects(
            $this->once())->method("resolveToResource")->willReturn(
                new Resource($this->getMailAccount()));

        $resource = $resourceResolver->resolve($request);

        $this->assertEquals(
            $this->getMailAccount()->toJson($this->getJsonApiStrategy()),
            $resource->toJson($this->getJsonApiStrategy())
        );
    }


    protected function getMailAccount(): MailAccount {
        return new MailAccount([
            "id"              => "1",
            "name"            => "conjoon developer",
            "from"            => ["name" => "John Smith", "address" => "dev@conjoon.org"],
            "replyTo"         => ["name" => "John Smith", "address" => "dev@conjoon.org"],
            "inbox_type"      => "IMAP",
            "inbox_address"   => "server.inbox.com",
            "inbox_port"      => 993,
            "inbox_user"      => "inboxUser",
            "inbox_password"  => "inboxPassword",
            "inbox_ssl"       => true,
            "outbox_address"  => "server.outbox.com",
            "outbox_port"     => 993,
            "outbox_user"     => "outboxUser",
            "outbox_password" => "outboxPassword",
            "outbox_secure"   => "ssl"
        ]);
    }
}

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
use Conjoon\Core\ParameterBag;
use Conjoon\Data\Resource\Exception\UnknownResourceException;
use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\Data\Resource\ResourceDescriptionList;
use Conjoon\Illuminate\Auth\ImapUser;
use Conjoon\MailClient\Data\MailAccount;
use Conjoon\MailClient\JsonApi\ResourceResolver;
use Conjoon\JsonApi\Resource\ResourceResolver as JsonApiResourceResolver;
use Conjoon\Net\Uri\Component\Path\ParameterList;
use Tests\TestCase;

class ResourceResolverTest extends TestCase
{
    public function testClass(): void
    {
        $this->assertInstanceOf(JsonApiResourceResolver::class, new ResourceResolver($this->getUser()));
    }

    public function testUnknowResourceException() {
        $this->expectException(UnknownResourceException::class);

        $resourceResolver = new ResourceResolver($this->getUser());

        $resolveToResource = $this->makeAccessible($resourceResolver, "resolveToResource");

        $resolveToResource->invokeArgs($resourceResolver, [
            $this->getDummyResourceDescription(),
            new ParameterList(),
            new ParameterBag()
        ]);
    }

    private function getUser(): ImapUser {
        $mailAccount = new MailAccount(["id" => "foo"]);

        return new ImapUser("", "", $mailAccount);
    }

    private function getDummyResourceDescription(): ResourceDescription {

        return new class extends ResourceDescription {

            public function getType(): string
            {
                return "dummy";
            }

            public function getRelationships(): ResourceDescriptionList
            {
                return new ResourceDescriptionList();
            }

            public function getFields(): array
            {
                return [];
            }

            public function getDefaultFields(): array
            {
                return [];
            }
        };

    }
}

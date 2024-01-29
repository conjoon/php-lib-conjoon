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

namespace Tests\Conjoon\Net\Uri\Component\Path;

use Conjoon\JsonApi\Query\Validation\CollectionQueryValidator;
use Conjoon\JsonApi\Query\Validation\JsonApiQueryValidator;
use Conjoon\MailClient\Data\CompoundKey\FolderKey;
use Conjoon\MailClient\Data\CompoundKey\MessageKey;
use Conjoon\MailClient\Data\Resource\MessageItemDescription;
use Conjoon\MailClient\JsonApi\MessageItem\MessageItemPathMatcher;
use Conjoon\Net\Uri;
use Tests\TestCase;


class MessageItemPathMatcherTest extends TestCase
{
    /**
     * Tests class & types
     * @return void
     */
    public function testClass(): void
    {
        $this->assertSame(
            "MailAccounts/{mailAccountId}/MailFolders/{mailFolderId}/MessageItems/{messageItemId}",
            MessageItemPathMatcher::TEMPLATE);
    }


    /**
     * tests match()
     * @return void
     */
    public function testMatch(): void
    {
        $matcher = new MessageItemPathMatcher();

        // single result
        $uri = Uri::make("https://localhost:8080/rest-api/MailAccounts/1/MailFolders/2/MessageItems/3?query=value");
        $result = $matcher->match($uri);
        $this->assertNotNull($result);
        $this->assertInstanceOf(MessageItemDescription::class, $result->getResourceDescription());
        $this->assertInstanceOf(JsonApiQueryValidator::class, $result->getQueryValidator());

        $this->assertFalse($result->isCollection());
        $this->assertInstanceOf(MessageKey::class, $result->getCompoundKey());
        $this->assertEquals([
            "mailAccountId" => "1",
            "mailFolderId" => "2",
            "id" => "3"
        ], $result->getCompoundKey()->toArray());

        // collection
        $uri = Uri::make("https://localhost:8080/rest-api/MailAccounts/1/MailFolders/2/MessageItems?query=value");
        $result = $matcher->match($uri);
        $this->assertNotNull($result);
        $this->assertTrue($result->isCollection());
        $this->assertInstanceOf(MessageItemDescription::class, $result->getResourceDescription());
        $this->assertInstanceOf(CollectionQueryValidator::class, $result->getQueryValidator());
        $this->assertInstanceOf(FolderKey::class, $result->getCompoundKey());
        $this->assertEquals([
            "mailAccountId" => "1",
            "id" => "2"
        ], $result->getCompoundKey()->toArray());
    }
}

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


use Conjoon\Http\RequestMethod;
use Conjoon\JsonApi\Request as JsonApiRequest;
use Conjoon\MailClient\Data\Resource\MessageItem;
use Conjoon\MailClient\JsonApi\MessageItem\PathMatcher;
use Conjoon\MailClient\JsonApi\MessageItem\MessageItemQueryValidator;
use Conjoon\Net\Url;
use Tests\TestCase;


class MessageItemTest extends TestCase
{

    public function testGetMessageItem() {

        $url = Url::make("https://localhost:8080/rest-api/v1/MailAccounts/1/MailFolders/INBOX/MessageItems/1");
        
        $request = new JsonApiRequest(
            $url, 
            RequestMethod::GET, 
            new MessageItem(),
            new MessageItemQueryValidator()
        );

        $this->assertSame(0, $request->validate()->count());

        $this->assertNull($request->getQuery());
    }


}

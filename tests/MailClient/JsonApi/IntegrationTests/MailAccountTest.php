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

use Conjoon\Http\Request;
use Conjoon\Http\RequestMethod;
use Conjoon\MailClient\JsonApi\MessageItem\MessageItemRequestMatcher;
use Conjoon\MailClient\JsonApi\RequestMatcher;
use Conjoon\Net\Url;
use Tests\TestCase;

class MailAccountTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Returning all MailAccounts is tied to the User, not a repository.
     *
     * @return void
     */
    public function testGetMailAccounts()
    {

        $url = Url::make("https://localhost:8080/rest-api/v1/MailAccounts");


        $httpRequest = new Request($url, RequestMethod::GET);
        $jsonApiRequest = $this->getRequestMatcher()->match($httpRequest);

        $this->assertNotNull($jsonApiRequest);
    }


    protected function getRequestMatcher(): RequestMatcher
    {
        return new RequestMatcher();
    }
}

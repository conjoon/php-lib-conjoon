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

namespace Tests\Conjoon\JsonApi\Resource;

use Conjoon\Data\Validation\ValidationError;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Illuminate\Auth\ImapUser;
use Conjoon\JsonApi\Request;
use Conjoon\MailClient\Data\MailAccount;
use Conjoon\MailClient\JsonApi\ResourceResolver;
use Conjoon\MailClient\Service\MailFolderService;
use Conjoon\Net\Uri\Component\Path\ParameterList;
use Conjoon\Net\Url;
use Conjoon\Web\Validation\Exception\ValidationException;
use Conjoon\Http\Request as HttpRequest;
use Tests\TestCase;

class ResourceResolverTest extends TestCase
{


    public function testValidationException() {
        $this->expectException(ValidationException::class);

        $jsonApiRequest = $this->createMockForAbstract(
            Request::class, ["validate"],
            [new HttpRequest(Url::make("https://localhost/folder")), new ParameterList()]

        );
        $jsonApiRequest->expects($this->once())->method("validate")->willReturn(
            ValidationErrors::make(
                ValidationError::make(new \stdClass())
            )
        );

        $this->getResourceResolver()->resolve($jsonApiRequest);
    }

    private function getResourceResolver(): ResourceResolver {
        return new ResourceResolver(
            $this->getUser(),
            $this->createMockForAbstract(MailFolderService::class)
        );
    }

    private function getUser(): ImapUser {
        $mailAccount = new MailAccount(["id" => "foo"]);

        return new ImapUser("", "", $mailAccount);
    }
}
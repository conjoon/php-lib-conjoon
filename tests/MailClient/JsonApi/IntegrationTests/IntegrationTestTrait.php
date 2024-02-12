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
use Conjoon\Http\Header;
use Conjoon\Http\HeaderList;
use Conjoon\Http\Request;
use Conjoon\Http\RequestMethod;
use Conjoon\Illuminate\Auth\ImapUser;
use Conjoon\MailClient\Data\CompoundKey\FolderKey;
use Conjoon\MailClient\Data\MailAccount;
use Conjoon\MailClient\Data\Transformer\Response\JsonApiStrategy as MailClientJsonApiStrategy;
use Conjoon\MailClient\Folder\MailFolder;
use Conjoon\MailClient\Folder\MailFolderChildList;
use Conjoon\MailClient\JsonApi\RequestMatcher;
use Conjoon\MailClient\JsonApi\ResourceResolver as MailClientResourceResolver;
use Conjoon\MailClient\Service\MailFolderService;
use Conjoon\Net\Url;
use Conjoon\JsonApi\Request as JsonApiRequest;

trait IntegrationTestTrait
{

    protected function getRequestMatcher(): RequestMatcher
    {
        return new RequestMatcher();
    }


    /**
     * @param string $url
     * @return JsonApiRequest|array
     *
     * @throws NotFoundException
     */
    protected function buildJsonApiRequest(string $url): JsonApiRequest|array {
        $url = Url::make($url);

        $httpRequest = new Request($url, RequestMethod::GET, HeaderList::make(
            Header::make(
                "accept",
                "application/vnd.api+json;ext=\"https://conjoon.org/json-api/ext/relfield\""
            )));
        $jsonApiRequest = $this->getRequestMatcher()->match($httpRequest);

        return $jsonApiRequest;
    }

    protected function getJsonApiStrategy(): MailClientJsonApiStrategy {
        return new MailClientJsonApiStrategy();
    }

    protected function getResourceResolver(): MailClientResourceResolver {
        $imapUser = $this->getIntegrationTestUser();

        return new MailClientResourceResolver(
            $imapUser,
            $this->getMockForMailFolderService()
        );
    }

    protected function getIntegrationTestUser(): ImapUser {
        return $this->getMockForAbstractClass(ImapUser::class, [
            "", "", $this->getMailAccount()
        ]);
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

    protected function getMockForMailFolderService():MailFolderService {

        $mailFolderChildList = $this->getMailFolderChildList();

        $mailFolderService = $this->createMockForAbstract(MailFolderService::class, ["getMailFolderChildList"]);

        $mailFolderService->expects($this->any())->method("getMailFolderChildList")->with(
            $this->callback(
                function (MailAccount $mailAccount)  {
                    $this->assertSame($mailAccount->getId(), $this->getMailAccount()->getId());
                    return true;
                }
            )
        )->willReturn($mailFolderChildList);

        return $mailFolderService;

    }

    private function getMailFolderChildList(): MailFolderChildList {
        $mailFolderChildList = new MailFolderChildList();
        $mailFolderChildList[] = new MailFolder(FolderKey::new("1", "2"), ["name" => "__int_1__"]);
        $mailFolderChildList[] = new MailFolder(FolderKey::new("1", "3"), ["name" => "__int_2__"]);

        return $mailFolderChildList;
    }
}

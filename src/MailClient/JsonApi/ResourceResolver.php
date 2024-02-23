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

namespace Conjoon\MailClient\JsonApi;

use Conjoon\Core\Util\ArrayUtil;
use Conjoon\Data\ParameterBag;
use Conjoon\Data\Resource\Exception\UnknownResourceException;
use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\Illuminate\Auth\ImapUser;
use Conjoon\JsonApi\Resource\Exception\ResourceNotFoundException;
use Conjoon\JsonApi\Resource\Exception\UnexpectedResolveException;
use Conjoon\JsonApi\Resource\Resource;
use Conjoon\JsonApi\Resource\ResourceResolver as JsonApiResourceResolver;
use Conjoon\MailClient\Data\CompoundKey\FolderKey;
use Conjoon\MailClient\Data\MailAccount;
use Conjoon\MailClient\Data\Resource\Query\DefaultMailFolderListQuery;
use Conjoon\MailClient\Data\Resource\Query\DefaultMessageItemListQuery;
use Conjoon\MailClient\Message\MessageItem;
use Conjoon\MailClient\Service\MailFolderService;
use Conjoon\MailClient\Service\MessageItemService;
use Conjoon\Net\Uri\Component\Path\ParameterList;
use Conjoon\MailClient\Data\Resource\Query\MailFolderListQuery;


class ResourceResolver extends JsonApiResourceResolver {

    private ImapUser $user;

    private MailFolderService $mailFolderService;

    private MessageItemService $messageItemService;

    public function __construct(
        ImapUser $user,
        MailFolderService $mailFolderService,
        MessageItemService $messageItemService
    ) {
        $this->user = $user;
        $this->mailFolderService = $mailFolderService;
        $this->messageItemService = $messageItemService;
    }

    /**
     * @Override
     */
    protected function resolveToResource(
        ResourceDescription $resourceDescription,
        ParameterList $pathParameters,
        ?ParameterBag $parameterBag
    ): Resource {

        if ($resourceDescription->getType() === "MailAccount") {
            return new Resource($this->user->getMailAccounts());
        }

        if ($resourceDescription->getType() === "MailFolder") {
            return $this->resolveToMailFolder($pathParameters, $parameterBag);
        }

        if ($resourceDescription->getType() === "MessageItem") {
            return $this->resolveToMessageItem($pathParameters, $parameterBag);
        }

        throw new UnknownResourceException(
            "Cannot resolve to ResourceDescription: \"{$resourceDescription}\""
        );

    }

    private function resolveToMessageItem(ParameterList $pathParameters, ?ParameterBag $parameterBag): Resource {
        $pps = $pathParameters->toArray();

        ["mailAccountId" => $mailAccountId,
        "mailFolderId" => $mailFolderId] = $this->getPathParameters(["mailAccountId", "mailFolderId"], $pps);

        $mailAccount = $this->getMailAccount($mailAccountId);

        $parameterBag = $parameterBag ?? new ParameterBag();

        return new Resource($this->messageItemService->getMessageItemList(
            FolderKey::new($mailAccount, $mailFolderId),
            new DefaultMessageItemListQuery($parameterBag)
        ));

    }

    private function resolveToMailFolder(ParameterList $pathParameters, ?ParameterBag $parameterBag): Resource {

        $pps = $pathParameters->toArray();

        ["mailAccountId" => $mailAccountId] = $this->getPathParameters(["mailAccountId"], $pps);

        $mailAccount = $this->getMailAccount($mailAccountId);
        $parameterBag = $parameterBag ?? new ParameterBag();

        return new Resource($this->mailFolderService->getMailFolderChildList(
            $mailAccount,
            new DefaultMailFolderListQuery($parameterBag)
        ));
    }


    /**
     * @param string $mailAccountId
     * @return MailAccount|null
     *
     * @throws ResourceNotFoundException
     */
    private function getMailAccount(string $mailAccountId): ?MailAccount {
        $mailAccount = $this->user->getMailAccount($mailAccountId);

        if (!$mailAccount) {
            throw new ResourceNotFoundException(
                "No MailAccount for the specified \"mailAccountId\" available. ".
                "\"mailAccountId\" was {$mailAccountId}");
        }

        return $mailAccount;
    }


    /**
     * @param array $keys
     * @param array $haystack
     * @return array
     *
     * @throws UnexpectedResolveException
     */
    private function getPathParameters(array $keys, array $haystack): array {
        if (!ArrayUtil::hasOnly($haystack, $keys)) {
            throw new UnexpectedResolveException(
                "Expected ". implode(",", $keys) ." in path parameters"
            );
        }

        return ArrayUtil::only($haystack, $keys);
    }

}


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

use Conjoon\Core\ParameterBag;
use Conjoon\Data\Resource\Exception\UnknownResourceException;
use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\Illuminate\Auth\ImapUser;
use Conjoon\JsonApi\Resource\Exception\ResourceNotFoundException;
use Conjoon\JsonApi\Resource\Exception\UnexpectedResolveException;
use Conjoon\JsonApi\Resource\Resource;
use Conjoon\JsonApi\Resource\ResourceResolver as JsonApiResourceResolver;
use Conjoon\MailClient\Service\MailFolderService;
use Conjoon\Net\Uri\Component\Path\ParameterList;


class ResourceResolver extends JsonApiResourceResolver {

    private ImapUser $user;

    private MailFolderService $mailFolderService;

    public function __construct(ImapUser $user, MailFolderService $mailFolderService) {
        $this->user = $user;
        $this->mailFolderService = $mailFolderService;
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

        throw new UnknownResourceException(
            "Cannot resolve to ResourceDescription: \"{$resourceDescription}\""
        );

    }


    private function resolveToMailFolder(ParameterList $pathParameters, ?ParameterBag $parameterBag): Resource {

        $pps = $pathParameters->toArray();

        if (!array_key_exists("mailAccountId", $pps)) {
            throw new UnexpectedResolveException("Expected \"mailAccountId\" in path parameters");
        }
        $mailAccountId = $pps["mailAccountId"];

        $mailAccount = $this->user->getMailAccount($mailAccountId);

        if (!$mailAccount) {
            throw new ResourceNotFoundException(
                "No MailAccount for the specified \"mailAccountId\" available. ".
                "\"mailAccountId\" was {$mailAccountId}");
        }

        $this->mailFolderService->getMailFolderChildList(
            $mailAccount, new MailFolderListQuery($parameterBag)
        );


    }

}


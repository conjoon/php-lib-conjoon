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
use Conjoon\JsonApi\Resource\Resource;
use Conjoon\JsonApi\Resource\ResourceResolver as JsonApiResourceResolver;
use Conjoon\Net\Uri\Component\Path\ParameterList;


class ResourceResolver extends JsonApiResourceResolver {

    private ImapUser $user;

    public function __construct(ImapUser $user) {
        $this->user = $user;
    }

    /**
     * @Override
     */
    public function resolveToResource(
        ResourceDescription $resourceDescription,
        ParameterList $pathParameters,
        ?ParameterBag $parameterBag
    ): Resource {

        if ($resourceDescription->getType() === "MailAccount") {
            return new Resource($this->user->getMailAccounts());
        }

        throw new UnknownResourceException(
            "Cannot resolve to ResourceDescription: \"{$resourceDescription}\" - unknown."
        );

    }

}


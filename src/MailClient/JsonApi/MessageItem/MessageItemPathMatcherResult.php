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

namespace Conjoon\MailClient\JsonApi\MessageItem;

use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\JsonApi\PathMatcherResult;
use Conjoon\JsonApi\Query\Validation\CollectionQueryValidator;
use Conjoon\JsonApi\Query\Validation\JsonApiQueryValidator;
use Conjoon\MailClient\Data\CompoundKey\CompoundKey;
use Conjoon\MailClient\Data\CompoundKey\FolderKey;
use Conjoon\MailClient\Data\CompoundKey\MessageKey;
use Conjoon\MailClient\Data\Resource\MessageItem;

final class MessageItemPathMatcherResult extends PathMatcherResult
{
    private CompoundKey $compoundKey;

    public function __construct(
        array $pathParameters
    ) {

        if (array_key_exists("messageItemId", $pathParameters)) {
            $this->collection = false;
            $this->compoundKey = new MessageKey(
                $pathParameters["mailAccountId"],
                $pathParameters["mailFolderId"],
                $pathParameters["messageItemId"]
            );
        } else {
            $this->collection = true;
            $this->compoundKey = new FolderKey(
                $pathParameters["mailAccountId"],
                $pathParameters["mailFolderId"]
            );
        }
    }

    public function isCollection(): bool
    {
        return $this->collection;
    }

    public function getCompoundKey(): MessageKey|FolderKey
    {
        return $this->compoundKey;
    }

    public function getResourceDescription(): ResourceDescription
    {
        return new MessageItem();
    }

    public function getQueryValidator(): JsonApiQueryValidator
    {
        return $this->collection ? new CollectionQueryValidator() : new JsonApiQueryValidator();
    }
}
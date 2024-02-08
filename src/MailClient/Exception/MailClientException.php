<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2019-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Conjoon\MailClient\Exception;

use Conjoon\Data\Resource\Exception\RepositoryException;
use Exception;

/**
 * Class MailClientException
 *
 * @package Conjoon\MailClient\Exception
 */
class MailClientException extends RepositoryException implements OwningException
{
    private Exception $ownedException;

    public function __construct(string|Exception $ownedException, int $code = 0, \Throwable $previous = null) {
        if (is_string($ownedException)) {
            parent::__construct($ownedException, $code, $previous);
            return;
        }
        $this->ownedException = $ownedException;
    }

    public function getOwnedException(): Exception
    {
        return $this->ownedException;
    }
}

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

namespace Conjoon\Data\Resource\Exception;

use RuntimeException;

/**
 * Represents an exception that is thrown when errors are generated during querying a repository.
 */
abstract class RepositoryException extends RuntimeException
{
}

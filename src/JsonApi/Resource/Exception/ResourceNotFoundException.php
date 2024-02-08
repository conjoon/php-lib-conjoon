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

namespace Conjoon\JsonApi\Resource\Exception;


use Conjoon\Http\Exception\NotFoundException;

/**
 * Exception indicates that a resource could not be found.
 */
class ResourceNotFoundException extends NotFoundException
{

}

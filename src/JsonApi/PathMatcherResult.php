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

namespace Conjoon\JsonApi;

use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\JsonApi\Query\Validation\JsonApiQueryValidator;

abstract class PathMatcherResult
{

    public abstract function isCollection() :bool;

    public abstract function getResourceDescription(): ResourceDescription;

    public abstract function getQueryValidator(): ?JsonApiQueryValidator;
}
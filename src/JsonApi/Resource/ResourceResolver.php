<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2022-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Conjoon\JsonApi\Resource;

use Conjoon\Core\Contract\Jsonable;
use Conjoon\Core\ParameterBag;
use Conjoon\Data\Resource\Exception\NotFoundException as ResourceNotFoundException;
use Conjoon\Data\Resource\Exception\RepositoryException;
use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\Net\Uri\Component\Path\ParameterList;


abstract class ResourceResolver {


    /**
     * Resolves the specified $resourceDescription to a Jsonable object. Further
     * details about the resource to resolve are provided with the specified $pathParameters
     * and the specified $parameterBag.
     *
     * @param ResourceDescription $resourceDescription
     * @param ParameterList $pathParameters
     * @param ParameterBag|null $parameterBag
     * @return Jsonable|null
     *
     * @throws ResourceNotFoundException|RepositoryException
     */
    abstract public function resolve(
        ResourceDescription $resourceDescription,
        ParameterList $pathParameters,
        ?ParameterBag $parameterBag
    ): ?Jsonable;

}


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
use Conjoon\Data\Resource\Exception\UnknownResourceException;
use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\JsonApi\Exception\BadRequestException;
use Conjoon\JsonApi\Request;
use Conjoon\Net\Uri\Component\Path\ParameterList;


abstract class ResourceResolver {

    /**
     * Resolves the specified $jsonApiRequest to a Resource.
     *
     * @param Request $jsonApiRequest
     * @return Resource
     *
     * @throws ResourceNotFoundException|RepositoryException|UnknownResourceException
     *
     * @see resolveToResource
     */
    public function resolve(Request $jsonApiRequest): Resource {

        $errors = $jsonApiRequest->validate();

        if ($errors->count() > 0) {
            $exc = new BadRequestException();
            $exc->setErrors($errors);
            throw $exc;
        }

        $resource = $this->resolveToResource(
            $jsonApiRequest->getResourceDescription(),
            $jsonApiRequest->getPathParameters(),
            $jsonApiRequest->getQuery()?->getParameterBag()
        );

        return $resource;
    }

    /**
     * Resolves the specified $resourceDescription to a Resource. Further
     * details about the resource to resolve are provided with the specified $pathParameters
     * and the specified $parameterBag.
     *
     * @param ResourceDescription $resourceDescription
     * @param ParameterList $pathParameters
     * @param ParameterBag|null $parameterBag
     * @return Resource
     *
     * @throws ResourceNotFoundException|RepositoryException|UnknownResourceException
     */
    abstract protected function resolveToResource(
        ResourceDescription $resourceDescription,
        ParameterList $pathParameters,
        ?ParameterBag $parameterBag
    ): Resource;

}


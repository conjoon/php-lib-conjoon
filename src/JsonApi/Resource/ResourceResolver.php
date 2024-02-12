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

use Conjoon\Data\ParameterBag;
use Conjoon\Data\Resource\Exception\RepositoryException;
use Conjoon\Data\Resource\Exception\UnknownResourceException;
use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\JsonApi\Request;
use Conjoon\JsonApi\Resource\Exception\ResourceNotFoundException;
use Conjoon\JsonApi\Resource\Exception\UnexpectedResolveException;
use Conjoon\Net\Uri\Component\Path\ParameterList;
use Conjoon\Web\Validation\Exception\ValidationException;


abstract class ResourceResolver {

    /**
     * Resolves the specified $jsonApiRequest to a Resource.
     *
     * @param Request $jsonApiRequest
     * @return Resource
     *
     * @throws ValidationException|ResourceNotFoundException|RepositoryException|UnknownResourceException|UnexpectedResolveException
     *
     * @see resolveToResource
     */
    public function resolve(Request $jsonApiRequest): Resource {

        $errors = $jsonApiRequest->validate();

        if ($errors->count() > 0) {
            $exc = new ValidationException();
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
     * @throws RepositoryException|ResourceNotFoundException|UnknownResourceException|UnexpectedResolveException
     */
    abstract protected function resolveToResource(
        ResourceDescription $resourceDescription,
        ParameterList $pathParameters,
        ?ParameterBag $parameterBag
    ): Resource;

}


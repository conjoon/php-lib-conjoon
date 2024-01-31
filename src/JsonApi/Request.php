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

namespace Conjoon\JsonApi;

use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Http\Request as HttpRequest;
use Conjoon\JsonApi\Query\Query as JsonApiQuery;
use Conjoon\JsonApi\Query\Validation\CollectionQueryValidator;
use Conjoon\JsonApi\Query\Validation\QueryValidator;
use Quant\Core\Attribute\Getter;
use Quant\Core\Trait\AccessorTrait;
use Conjoon\Net\Uri\Component\Path\ParameterList as PathParameters;

/**
 * Request specific for JSON:API requests.
 */
class Request extends HttpRequest
{
    use AccessorTrait;

    #[Getter]
    private PathParameters $pathParameters;

    #[Getter]
    private ?QueryValidator $queryValidator;

    #[Getter]
    private ?ResourceDescription $resourceDescription;

    /**
     * Constructor.
     *
     * @param HttpRequest $request
     * @param PathParameters $pathParameters
     * @param ResourceDescription|null $resourceDescription
     * @param QueryValidator|null $queryValidator
     */
    public function __construct(
        HttpRequest          $request,
        PathParameters       $pathParameters,
        ?ResourceDescription $resourceDescription = null,
        ?QueryValidator      $queryValidator = null,
     ) {
        parent::__construct($request->getUrl(), $request->getMethod());

        $this->pathParameters = $pathParameters;
        $this->request = $request;
        $this->resourceDescription = $resourceDescription;
        $this->queryValidator = $queryValidator;
    }


    /**
     * Returns true if this request targets a collection of the specified ResourceDescription.
     * @return bool
     */
    public function targetsCollection(): bool {
        return $this->queryValidator &&
            ($this->queryValidator instanceof CollectionQueryValidator);
    }

    /**
     * Validates the request.
     * Will return an ValidationErrors-collection with details about all the errors that were found.
     *
     * @return ValidationErrors
     */
    public function validate(): ValidationErrors
    {
        $errors = new ValidationErrors();
        $validator = $this->getQueryValidator();
        $query = $this->getQuery();

        if (!$validator || !$query) {
            return $errors;
        }

        $validator->validate($query, $errors);

        return $errors;
    }

    /**
     * @Override
     */
    public function getQuery(): ?JsonApiQuery
    {
        if (isset($this->query)) {
            /**
             * @type Query|null
             */
            return $this->query;
        }

        if (!$this->url->getQuery()) {
            $this->query = null;
        } else {
            $this->query = new JsonApiQuery(
                $this->url->getQuery(),
                $this->getResourceDescription()
            );
        }

        return $this->query;
    }
}

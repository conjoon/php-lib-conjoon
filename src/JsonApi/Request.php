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

use Conjoon\Data\Resource\ObjectDescription;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Http\Request as HttpRequest;
use Conjoon\Http\RequestMethod as HttpMethod;
use Conjoon\JsonApi\Query\Validation\Validator;
use Conjoon\Net\Uri\Component\Query;
use Conjoon\Net\Url;
use Conjoon\JsonApi\Query\Query as JsonApiQuery;

/**
 * Request specific for JSON:API requests.
 *
 */
class Request extends HttpRequest
{
    /**
     * @var Validator|null
     */
    private ?Validator $queryValidator;


    private ObjectDescription $objectDescription;

    /**
     * Constructor.
     *
     * @param Url $url
     * @param HttpMethod $method
     * @param Validator|null $queryValidator
     */
    public function __construct(
        Url $url,
        HttpMethod $method,
        ObjectDescription $objectDescription,
        Validator $queryValidator = null
    ) {
        parent::__construct($url, $method);
        $this->objectDescription = $objectDescription;
        $this->queryValidator = $queryValidator;
    }


    /**
     * Returns the query validator for this request, if any was specified with the constructor.
     */
    protected function getQueryValidator(): ?Validator
    {
        return $this->queryValidator;
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
     * @return JsonApiQuery|null
     */
    public function getQuery(): ?JsonApiQuery
    {
        if (!$this->url->getQuery()) {
            return null;
        }
        return new JsonApiQuery($this->url->getQuery(), $this->objectDescription);
    }
}

<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge,
 * publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
 * USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

declare(strict_types=1);

namespace Conjoon\JsonApi\Request;

use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\Http\Query\Query;
use Conjoon\Http\Request\Request as HttpRequest;
use Conjoon\JsonApi\Resource\ObjectDescription;
use Conjoon\JsonApi\Query\Query as JsonApiQuery;
use Conjoon\JsonApi\Query\Validation\Validator;

/**
 * Request specific for JSON:API, containing resource target ObjectDescriptions.
 *
 */
class Request implements HttpRequest
{
    /**
     * @var HttpRequest
     */
    protected HttpRequest $request;


    /**
     * @var ObjectDescription
     */
    protected ObjectDescription $resourceTarget;


    /**
     * @var Query|null
     */
    protected ?Query $query = null;


    /**
     * @var Validator|null
     */
    private ?Validator $queryValidator;


    /**
     * Constructor.
     *
     * @param Request $request
     * @param ObjectDescription $resourceTarget
     * @param Validator|null $queryValidator
     */
    public function __construct(
        HttpRequest $request,
        ObjectDescription $resourceTarget,
        Validator $queryValidator = null
    ) {
        $this->request = $request;
        $this->resourceTarget = $resourceTarget;
        $this->queryValidator = $queryValidator;
    }


    /**
     * @inheritdoc
     */
    public function getQuery(): ?Query
    {
        if ($this->query) {
            return $this->query;
        }

        $query = $this->request->getQuery();

        if (!$query) {
            return null;
        }

        $resourceTarget = $this->getResourceTarget();

        $this->query = new JsonApiQuery($query, $resourceTarget);

        return $this->query;
    }


    /**
     * @inheritdoc
     */
    public function getMethod(): string
    {
        return $this->request->getMethod();
    }


    /**
     * @inheritdoc
     */
    public function getUrl(): string
    {
        return $this->request->getUrl();
    }


    /**
     * Returns the resource target this request is interested in.
     *
     * @return ObjectDescription
     */
    public function getResourceTarget(): ObjectDescription
    {
        return $this->resourceTarget;
    }


    /**
     * Returns the query validator for this request, if any was specified with the constructor.
     */
    public function getQueryValidator(): ?Validator
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

        if (!$validator) {
            return $errors;
        }

        $query = $this->getQuery();
        $validator->validate($query, $errors);

        return $errors;
    }
}

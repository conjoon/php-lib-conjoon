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

namespace Conjoon\JsonApi;

use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Http\Request as HttpRequest;
use Conjoon\Http\RequestMethod as HttpMethod;
use Conjoon\JsonApi\Query\Validation\Validator;
use Conjoon\Net\Url;

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


    /**
     * Constructor.
     *
     * @param Url $url
     * @param HttpMethod $method
     * @param Validator|null $queryValidator
     */
    public function __construct(
        Url $url,
        HttpMethod $method = HttpMethod::GET,
        Validator $queryValidator = null
    ) {
        parent::__construct($url, $method);
        $this->queryValidator = $queryValidator;
    }


    /**
     * Returns the query validator for this request, if any was specified with the constructor.
     */
    private function getQueryValidator(): ?Validator
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
}

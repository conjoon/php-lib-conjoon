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
use Conjoon\Http\RequestMethod as HttpMethod;
use Conjoon\JsonApi\Query\JsonApiQuery as JsonApiQuery;
use Conjoon\JsonApi\Query\Validation\JsonApiQueryValidator;
use Conjoon\Net\Url;

/**
 * Request specific for JSON:API requests.
 *
 */
class JsonApiRequest extends HttpRequest
{
    private ?PathMatcherResult $pathMatcherResult;
    private PathMatcher $pathMatcher;


    /**
     * Constructor.
     *
     * @param Url $url
     * @param HttpMethod $method
     * @param PathMatcher $pathMatcher
     */
    public function __construct(
        Url $url,
        HttpMethod $method,
        PathMatcher $pathMatcher
    ) {
        parent::__construct($url, $method);
        $this->pathMatcher = $pathMatcher;
    }

    protected function resolvePath() : ?PathMatcherResult {
        if (!isset($this->pathMatcherResult)) {
            $this->pathMatcherResult = $this->pathMatcher->match($this->getUrl());
        }

        return $this->pathMatcherResult;
    }

    public function getPathMatcherResult(): ?PathMatcherResult {
        return $this->resolvePath();
    }

    /**
     * Returns the Validator resolved for this request.
     */
    protected function getQueryValidator(): ?JsonApiQueryValidator
    {
        return $this->resolvePath()?->getQueryValidator();
    }

    /**
     * Returns the ResourceDescription resolved for this request.
     */
    protected function getResourceDescription(): ?ResourceDescription
    {
        return $this->resolvePath()?->getResourceDescription();
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
        if (isset($this->query)) {
            /**
             * @type JsonApiQuery|null
             */
            return $this->query;
        }

        if (!$this->url->getQuery()) {
            $this->query = null;
        } else {
            $this->query = new JsonApiQuery($this->url->getQuery(), $this->getResourceDescription());
        }

        return $this->query;
    }
}

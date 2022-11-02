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

namespace Conjoon\Http;

use Conjoon\Net\Uri\Component\Query;
use Conjoon\Net\Url;

/**
 * Represents an Http Request.
 *
 */
class Request
{
    /**
     * @var Url
     */
    private readonly Url $url;


    /**
     * @var Query|null
     */
    private ?Query $query = null;


    /**
     * @var RequestMethod
     */
    private readonly RequestMethod $method;


    /**
     * Constructor.
     *
     * @param Url $url
     * @param RequestMethod $method
     */
    public function __construct(Url $url, RequestMethod $method = RequestMethod::GET)
    {
        $this->url = $url;
        $this->method = $method;
    }


    /**
     * Get the URL for the request.
     *
     * @return Url
     */
    public function getUrl(): Url
    {
        return $this->url;
    }


    /**
     * Get the HTTP request method used with this request.
     *
     * @return RequestMethod
     */
    public function getMethod(): RequestMethod
    {
        return $this->method;
    }


    /**
     * Returns the Query-component of the url of this request, or null
     * if no query is available.
     *
     * @return Query|null
     */
    public function getQuery(): ?Query
    {
        if ($this->query) {
            return $this->query;
        }

        $query = $this->url->getQuery();
        if (!$query) {
            return null;
        }

        $this->query = new Query($query);
        return $this->query;
    }
}

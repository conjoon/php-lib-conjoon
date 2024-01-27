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
    protected readonly Url $url;


    /**
     * @var Query|null
     */
    protected ?Query $query = null;


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

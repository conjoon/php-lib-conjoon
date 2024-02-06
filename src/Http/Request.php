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
    protected ?Query $query;


    /**
     * @var RequestMethod
     */
    private readonly RequestMethod $method;

    private readonly HeaderList $headerList;

    /**
     * Constructor.
     *
     * @param Url $url
     * @param RequestMethod $method
     * @param HeaderList|null $header
     */
    public function __construct(Url $url, RequestMethod $method = RequestMethod::GET, ?HeaderList $header = null)
    {
        $this->url = $url;
        $this->method = $method;

        if ($header === null) {
            $this->headerList = new HeaderList();
        } else {
            $this->headerList = $header;
        }
    }

    public function getHeader(string $name): ?string {
        $match = $this->headerList->findBy(fn(Header $header) => strtolower($header->getName()) === strtolower($name));
        /**
         * @type Header $match
         */
        return $match?->getValue();

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
        if (isset($this->query)) {
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

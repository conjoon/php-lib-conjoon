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

use Conjoon\Core\Contract\Stringable;
use Conjoon\Core\Contract\StringStrategy;
use Conjoon\Http\Query\Query;

/**
 * Represents an URL.
 *
 */
class Url implements Stringable
{
    /**
     * @var ?Query
     */
    protected ?Query $query = null;


    /**
     * @var string
     */
    protected string $url;

    /**
     * @var bool
     */
    protected bool $queryBuild = false;

    /**
     * Constructor.
     *
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }


    /**
     * Returns the Query for this url.
     * If no query string was available for this url, null will be returned.
     *
     * @return Query|null
     */
    public function getQuery(): ?Query
    {
        if ($this->queryBuild) {
            return $this->query;
        }

        $this->queryBuild = true;
        $query = parse_url($this->url, PHP_URL_QUERY);
        if ($query) {
            $this->query = new Query($query);
        }

        return $this->query;
    }


    /**
     * @inheritdoc
     */
    public function toString(StringStrategy $stringStrategy = null): string
    {
        return $stringStrategy ? $stringStrategy->toString($this) : $this->url;
    }
}

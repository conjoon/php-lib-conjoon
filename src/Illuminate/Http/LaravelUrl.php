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

namespace Conjoon\Illuminate\Http;

use Conjoon\Http\Url;
use Conjoon\Core\Contract\StringStrategy;
use Conjoon\Illuminate\Http\Query\LaravelQuery;

/**
 * LaravelUrl as an adapter for url-strings.
 * Required by LaravelRequest.
 */
class LaravelUrl implements Url
{
    /**
     * @var string
     */
    protected string $url;

    /**
     * @var LaravelQuery
     */
    protected LaravelQuery $query;


    /**
     * Constructor.
     *
     * @param string $url
     * @param LaravelQuery $query
     */
    public function __construct(string $url, LaravelQuery $query)
    {
        $this->query   = $query;
        $this->url = $url;
    }


    /**
     * @inheritdoc
     */
    public function toString(StringStrategy $stringStrategy = null): string
    {
        return $stringStrategy ? $stringStrategy->toString($this) : $this->url;
    }


    /**
     * @inheritdoc
     */
    public function getQuery(): LaravelQuery
    {
        return $this->query;
    }
}

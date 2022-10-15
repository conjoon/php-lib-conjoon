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

namespace Conjoon\Http\Query;

use Conjoon\Core\Data\ParameterBag;
use Conjoon\Core\Data\Error\ErrorSource;

/**
 * Interface used to control Http-Queries.
 */
abstract class Query implements ErrorSource
{
    /**
     * Gets the parameter from this Query. Returns null if no parameter with the
     * name exists with this query.
     *
     * @param string $name
     *
     * @return Parameter|null
     */
    abstract public function getParameter(string $name): ?Parameter;


    /**
     * Returns a ParameterList containing all the parameters of this query.
     *
     * @return ParameterList
     */
    abstract public function getAllParameters(): ParameterList;


    /**
     * Returns an array containing all available parameter names of this query.
     * Returns an empty array if no parameters are available.
     *
     * @return array
     */
    abstract public function getAllParameterNames(): array;


    /**
     * Returns a ParameterBag containing all the data from this parameters.
     *
     * @return ParameterBag
     */
    public function getParameterBag(): ParameterBag
    {
        return new ParameterBag($this->getAllParameters()->toArray());
    }
}

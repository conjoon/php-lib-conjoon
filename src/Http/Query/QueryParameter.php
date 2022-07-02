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

use Conjoon\Http\Query\Exception\QueryException;

/**
 * Class QueryParameter
 * @package Conjoon\Http\Query
 */
abstract class QueryParameter
{
    /**
     * @var string|null
     */
    protected ?string $rawValue;

    /**
     * @var string|null
     */
    protected $value;


    /**
     * @param string|null $value
     */
    public function __construct(?string $value)
    {
        $this->rawValue = $value;
        $this->value    = $value;
    }

    /**
     * Returns the original parameters value with which this instance was created.
     *
     * @return string|null
     */
    public function getRawValue()
    {
        return $this->rawValue;
    }

    /**
     * Returns the value of this Parameter, which might be different than the
     * original value for which this instance was created. (@see #rawValue)
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Textual representation of this parameter's name.
     * @return string
     */
    abstract public function getName(): string;


    /**
     * Processes this parameter's value and assigns the $processor's return value
     * to #value.
     *
     * @param QueryParameterProcessor $processor
     * @return void
     *
     * @throws QueryException
     */
    public function validate(QueryParameterProcessor $processor): QueryParameter
    {
        $this->value = $processor->process($this);

        return $this;
    }
}

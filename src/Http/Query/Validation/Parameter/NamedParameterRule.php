<?php

/**
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

namespace Conjoon\Http\Query\Validation\Parameter;

/**
 * NamedParameterRule provides extended functionality for supports() base implementation
 * by returning true if, and only if ParameterRule's supports() returns true and if the name of
 * the parameter being validated is in the list of parameter names this rule was configured with.
 *
 */
abstract class NamedParameterRule extends ParameterRule
{
    /**
     * @var string|array
     */
    protected string|array $parameterName;

    /**
     * Constructor.
     *
     * @param string|array $parameterName The name of the parameter for which this rule should be
     * applied, or an array of names so the rule can be re-used with multiple parameters
     */
    public function __construct(string|array $parameterName)
    {
        $this->parameterName = $parameterName;
    }


    /**
     * @inheritdoc
     */
    public function supports(object $obj): bool
    {
        $names = is_array($this->parameterName) ? $this->parameterName : [$this->parameterName];

        return parent::supports($obj) &&
            in_array($obj->getName(), $names);
    }
}

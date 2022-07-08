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

namespace Conjoon\Http\Query\Validation;

use Conjoon\Core\Exception\UnexpectedTypeException;
use Conjoon\Core\Validation\Rule as ValidationRule;
use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\Http\Query\Exception\UnexpectedQueryParameterException;
use Conjoon\Http\Query\Parameter;

/**
 * Rule specific for QueryParameters
 */
abstract class ParameterRule implements ValidationRule
{
    /**
     * @inheritdoc
     *
     * @throws UnexpectedQueryParameterException if shouldValidateParameter() returns false for the
     *                                           Parameter passed to this method
     * @see validate()
     */
    public function isValid(object $obj, ValidationErrors $errors): bool
    {
        if (!$obj instanceof Parameter) {
            throw new UnexpectedTypeException();
        }

        if (!$this->shouldValidateParameter($obj)) {
            throw new UnexpectedQueryParameterException();
        }

        return $this->validate($obj, $errors);
    }

    /**
     * Contract for validating the passed QueryParameter.
     * Delegated from isValid.
     *
     * @param Parameter $parameter
     * @param ValidationErrors $errors
     *
     * @return bool true if validation succeeded, otherwise false
     */
    abstract protected function validate(Parameter $parameter, ValidationErrors $errors): bool;


    /**
     * Check whether the given parameter should be validated given this rule.
     *
     * @param Parameter $parameter
     *
     * @return bool
     */
    abstract public function shouldValidateParameter(Parameter $parameter): bool;
}

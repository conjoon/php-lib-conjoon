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

namespace Conjoon\Http\Query\Validation;

use Conjoon\Core\Exception\UnexpectedTypeException;
use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\Core\Validation\Validator as BaseValidator;
use Conjoon\Http\Query\Exception\UnexpectedQueryException;
use Conjoon\Http\Query\Query;

/**
 * Class for validating queries that must be checked for validity with QueryRules and ParameterRules.
 */
abstract class Validator implements BaseValidator
{
    /**
     * Return true if this class supports validating the specified Query, otherwise false.
     *
     * @param Query $query
     * @return bool
     */
    public function supports(Query $query): bool
    {
        return ($query instanceof Query);
    }


    /**
     * @inheritdoc
     *
     * @throws UnexpectedTypeException|UnexpectedQueryException if $obj is not an instance of Query,
     * or if the return value for the supplied $obj returns false
     */
    public function validate(object $obj, ValidationErrors $errors): void
    {
        if (!$obj instanceof Query) {
            throw new UnexpectedTypeException(
                "expected target object for validation to be instance of " . Query::class
            );
        }

        if (!$this->supports($obj)) {
            throw new UnexpectedQueryException(
                "query is not supported by this validator"
            );
        }

        foreach ($this->getQueryRules($obj) as $queryRule) {
            $queryRule->isValid($obj, $errors);
        }

        $parameters = $obj->getAllParameters();

        foreach ($this->getParameterRules($obj) as $parameterRule) {
            $parameters->map(
                fn ($parameter) => $parameterRule->shouldValidateParameter($parameter)
                                   ? $parameterRule->isValid($parameter, $errors)
                                   : null
            );
        }
    }


    /**
     * Returns the ParameterRules for the specified Query.
     *
     * @param Query $query
     *
     * @return array
     */
    abstract public function getParameterRules(Query $query): array;


    /**
     * Returns the QueryRules for the specified Query.
     *
     * @param Query $query
     *
     * @return array
     */
    abstract public function getQueryRules(Query $query): array;
}
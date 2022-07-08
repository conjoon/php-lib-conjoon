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

use Conjoon\Core\Validation\ValidationError;
use Conjoon\Http\Query\Parameter;
use Conjoon\Core\Validation\ValidationErrors;

/**
 * Class providing functionality for making sure a query's parameter contains only valid
 * values. Validation is done against a list of values specified with the constructor.
 */
class ValueInWhitelistRule extends ParameterRule
{
    /**
     * @var array
     */
    protected array $whitelist;

    /**
     * @var string
     */
    protected string $parameterName;


    /**
     * Constructor.
     *
     * @param string $parameterName The name of the parameter for which this rule should be applied
     * @param array $whitelist The list of allowed values that may appear with the value of the QueryParameter
     */
    public function __construct(string $parameterName, array $whitelist)
    {
        $this->parameterName = $parameterName;
        $this->whitelist = $whitelist;
    }

    /**
     * @inheritdoc
     */
    protected function validate(Parameter $parameter, ValidationErrors $errors): bool
    {
        $whitelist = $this->getWhitelist();
        if (!$this->isParameterValueValid($parameter, $whitelist)) {
            $errors[] = new ValidationError(
                $parameter,
                "parameter \"" . $parameter->getName() . "\"'s value must validate against \"" .
                implode("\", \"", $whitelist) . "\", was: \"" . $parameter->getValue() . "\"",
                400
            );
            return false;
        }

        return true;
    }


    /**
     * Compares $parameter with $whitelist.
     * $whitelist is the array of allowed values for the parameter's value.
     * Implementing APIs can use this method to transform the passed $parameter's value, if required.
     *
     * @param Parameter $parameter
     * @param array $whitelist
     *
     * @return bool
     */
    protected function isParameterValueValid(Parameter $parameter, array $whitelist): bool
    {
        return in_array($parameter->getValue(), $whitelist);
    }

    /**
     * @return array
     */
    public function getWhitelist(): array
    {
        return $this->whitelist;
    }

    /**
     * @inheritdoc
     */
    public function shouldValidateParameter(Parameter $parameter): bool
    {
        return $parameter->getName() === $this->parameterName;
    }
}

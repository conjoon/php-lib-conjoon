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

namespace Conjoon\JsonApi;

use Conjoon\Http\Query\Exception\UnexpectedQueryParameterException;
use Conjoon\Http\Query\Parameter;

/**
 * Utility class for various helper methods regarding parsing JSON:API queries and parameters.
 */
class Util
{
    /**
     * Unfolds possible dot notated types available with includes and returns it as
     * an array with all resource types as unique values in the resulting array.
     *
     * @example
     *    Util::unfoldInclude(
     *        new Parameter("include",
     *        "MailFolder.MailAccount,MailFolder"));  // ["MailAccount", "MailFolder"]
     *
     *
     * @param Parameter $parameter
     *
     * @return array
     *
     * @throws UnexpectedQueryParameterException if the parameter's name is not "include"
     */
    public static function unfoldInclude(Parameter $parameter): array
    {
        if ($parameter->getName() !== "include") {
            throw new UnexpectedQueryParameterException(
                "parameter passed does not seem to be the \"include\" parameter"
            );
        }

        $includes = $parameter->getValue() ? explode(",", $parameter->getValue()) : null;

        if (!$includes) {
            return [];
        }

        $res = [];
        foreach ($includes as $include) {
            $res = array_merge($res, explode(".", $include));
        }

        return array_values(array_unique($res));
    }
}

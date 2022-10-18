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

/**
 * Trait for parameter utilities.
 */
trait ParameterTrait
{
    /**
     * @const GROUP_REGEX
     */
    protected string $GROUP_REGEX = "/(.*)\[(.*)\]/m";


    /**
     * Returns true if the the passed string matches the grouped query parameters pattern, otherwise false.
     *
     * @example
     *    $this->isGroupParameter(new Parameter("fields[TYPE]", ""); // true
     *    $this->isGroupParameter(new Parameter("fields[]", ""); // true
     *    $this->isGroupParameter(new Parameter("fields", ""); // false
     *
     * @param string|Parameter $parameter
     *
     * @return bool
     */
    public function isGroupParameter(string|Parameter $parameter): bool
    {
        $name = is_string($parameter) ? $parameter : $parameter->getName();
        return !!preg_match($this->GROUP_REGEX, $name);
    }


    /**
     * Returns the name of the group if the passed string matches the grouped query parameters pattern.
     * Returns null if not matching the grouped query parameter pattern.
     *
     * @example
     *    $this->getGroupName(new Parameter("fields[TYPE]", ""); // "fields"
     *    $this->getGroupName(new Parameter("fields[]", ""); // "fields"
     *    $this->getGroupName(new Parameter("fields", ""); // null
     *
     * @param string|Parameter $parameter
     *
     * @return string|null
     */
    public function getGroupName(string|Parameter $parameter): ?string
    {
        $name = is_string($parameter) ? $parameter : $parameter->getName();

        $found = preg_match_all(
            $this->GROUP_REGEX,
            $name,
            $matches,
            PREG_SET_ORDER,
            0
        );

        if ($found) {
            return $matches[0][1];
        }

        return null;
    }


    /**
     * Returns the name of the key of the group if the passed string matches the grouped query parameters pattern.
     * Returns null if not matching the grouped query parameter pattern.
     *
     * @example
     *    $this->getGroupName(new Parameter("fields[TYPE]", ""); // "TYPE"
     *    $this->getGroupName(new Parameter("fields", ""); // null
     *
     * @param string|Parameter $parameter
     *
     * @return string|null
     */
    public function getGroupKey(string|Parameter $parameter): ?string
    {
        $name = is_string($parameter) ? $parameter : $parameter->getName();

        $found = preg_match_all(
            $this->GROUP_REGEX,
            $name,
            $matches,
            PREG_SET_ORDER,
            0
        );

        if ($found) {
            return $matches[0][2] !== "" ? $matches[0][2] : null;
        }

        return null;
    }
}

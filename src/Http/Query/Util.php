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
 * Utility class.
 */
final class Util
{
    /**
     * @const GROUP_REGEX
     */
    public const GROUP_REGEX = "/(.*)\[(.{1,})\]/m";


    /**
     * Returns true if the the passed string matches the grouped query parameters pattern, otherwise false.
     *
     * @example
     *    Util::isGroupParameter("fields[TYPE]"); // true
     *    Util::isGroupParameter("fields"); // false
     *
     * @param string $name
     * @return bool
     */
    public static function isGroupParameter(string $name): bool
    {
        return !!preg_match(self::GROUP_REGEX, $name);
    }


    /**
     * Returns the name of the group if the passed string matches the grouped query parameters pattern.
     * Returns null if not matching the grouped query parameter pattern.
     *
     * @example
     *    Util::getGroupName("fields[TYPE]"); // "fields"
     *    Util::getGroupName("fields"); // null
     *
     * @param string $name
     * @return string|null
     */
    public static function getGroupName(string $name): ?string
    {
        $found = preg_match_all(
            self::GROUP_REGEX,
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
}

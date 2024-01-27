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

namespace Conjoon\Core\Util;

use Conjoon\Core\Exception\ClassNotFoundException;
use Conjoon\Core\Exception\InvalidTypeException;

/**
 * Helper for creating instances of  classes based on strings representing fully qualified names.
 *
 */
class ClassLoader
{
    /**
     * Return the $fqn is the class exists and is of type $baseClass..
     *
     * @param string $fqn The fqn of the class that should be loaded
     * @param string $baseClass The base class the class to load must extend
     *
     * @return string
     *
     * @throws ClassNotFoundException|InvalidTypeException
     */
    public function load(string $fqn, string $baseClass): string
    {
        if (!class_exists($fqn)) {
            throw new ClassNotFoundException(
                "Cannot find class with name $fqn"
            );
        }

        if (!is_a($fqn, $baseClass, true)) {
            throw new InvalidTypeException(
                "$fqn must be an instance of $baseClass, was " . get_parent_class($fqn)
            );
        }

        return $fqn;
    }


    /**
     * @param string $fqn The fqn of the class that should be loaded
     * @param string $baseClass The base class the class to load must extend
     * @param array<int, mixed> $args
     *
     * @return mixed
     *
     * @throws ClassNotFoundException|InvalidTypeException
     */
    public function create(string $fqn, string $baseClass, array $args = []): mixed
    {
        $fqn = $this->load($fqn, $baseClass);

        return new $fqn(...$args);
    }
}

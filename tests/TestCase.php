<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2019-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Tests;

use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Class TestCase
 * @package Tests
 */
abstract class TestCase extends PHPUnitTestCase
{
    protected function createMockForAbstract(
        string $originalClassName,
        array $mockedMethods = [],
        array $args = []
    ): MockObject {
        return parent::getMockForAbstractClass(
            $originalClassName,
            $args,
            '',
            true,
            true,
            true,
            $mockedMethods
        );
    }


    /**
     * @param mixed $inst
     * @param $name
     * @param bool $isProperty
     *
     * @return ($isProperty is true ? ReflectionProperty : ReflectionMethod)
     *
     * @throws ReflectionException
     */
    protected function makeAccessible($inst, $name, bool $isProperty = false): ReflectionMethod|ReflectionProperty
    {
        $refl = new ReflectionClass($inst);

        $name = match ($isProperty) {
            true => $refl->getProperty($name),
            default => $refl->getMethod($name),
        };

        $name->setAccessible(true);

        return $name;
    }
}

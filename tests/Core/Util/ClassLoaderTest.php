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

namespace Tests\Conjoon\Core\Util;

use Conjoon\Core\Util\ClassLoader;
use Conjoon\Core\Exception\ClassNotFoundException;
use Conjoon\Core\Exception\InvalidTypeException;
use Tests\TestCase;

/**
 * Tests ClassLoader
 */
class ClassLoaderTest extends TestCase
{
    /**
     * tests load() with ClassNotFoundException
     */
    public function testGetResourceTargetWithClassNotFoundException()
    {
        $this->expectException(ClassNotFoundException::class);

        $loader = new ClassLoader();

        $loader->load("RandomClass", "RandomParentClass");
    }


    /**
     * tests load() with InvalidTypeException
     */
    public function testGetResourceTargetWithInvalidTypeException()
    {
        $this->expectException(InvalidTypeException::class);

        $loader = new ClassLoader();

        $loader->load("Tests\\Conjoon\\Data\\Resource\\TestResourceStd", "Conjoon\\Core");
    }

    /**
     * tests load()
     */
    public function testLoad()
    {
        $loader = new ClassLoader();

        $this->assertSame(
            "Tests\\Conjoon\\Data\\Resource\\TestResourceResourceDescription",
            $loader->load(
                "Tests\\Conjoon\\Data\\Resource\\TestResourceResourceDescription",
                "Conjoon\\Data\\Resource\\ResourceDescription"
            )
        );
    }


    /**
     * tests create()
     */
    public function testGetResourceTarget()
    {
        $loader = new ClassLoader();

        $inst = $loader->create(
            "Tests\\Conjoon\\Data\\Resource\\TestResourceResourceDescription",
            "Conjoon\\Data\\Resource\\ResourceDescription"
        );
        $this->assertNull($inst->getOne());
        $this->assertNull($inst->getTwo());
        $this->assertNull($inst->getThree());

        $this->assertInstanceOf(
            "Tests\\Conjoon\\Data\\Resource\\TestResourceResourceDescription",
            $inst
        );

        $loader = new ClassLoader();

        $inst = $loader->create(
            "Tests\\Conjoon\\Data\\Resource\\TestResourceResourceDescription",
            "Conjoon\\Data\\Resource\\ResourceDescription",
            [1, 2, 3]
        );
        $this->assertSame(1, $inst->getOne());
        $this->assertSame(2, $inst->getTwo());
        $this->assertSame(3, $inst->getThree());

        $this->assertInstanceOf(
            "Tests\\Conjoon\\Data\\Resource\\TestResourceResourceDescription",
            $inst
        );
    }
}

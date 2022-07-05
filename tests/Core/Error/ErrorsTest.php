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

namespace Tests\Conjoon\Core\Error;

use Conjoon\Core\Error\Errors;
use Conjoon\Core\Error\Error;
use Conjoon\Core\Data\AbstractList;
use Tests\TestCase;

/**
 * tests Errors
 */
class ErrorsTest extends TestCase
{
    /**
     * Tests constructor
     */
    public function testClass()
    {
        $list = $this->createList();
        $this->assertInstanceOf(AbstractList::class, $list);

        $this->assertSame(Error::class, $list->getEntityType());
    }


    /**
     * Tests to array
     */
    public function testHasError()
    {
        $list = $this->createList();
        $this->assertFalse($list->hasError());

        $list[] = $this->createMockForAbstract(Error::class);
        $this->assertTrue($list->hasError());
    }


    /**
     * @return Errors
     */
    protected function createList(): Errors
    {
        $list = new Errors();


        return $list;
    }
}
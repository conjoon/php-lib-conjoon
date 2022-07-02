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

namespace Tests\Conjoon\Http\Query;

use Conjoon\Http\Query\QueryParameter;
use Conjoon\Http\Query\QueryParameterProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Class ResourceQueryTest
 * @package Tests\Conjoon\Http\Query
 */
class QueryParameterTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $mock = $this->getQueryParameter(["getName"], ["value"]);

        $this->assertSame("value", $mock->getValue());
        $this->assertSame("value", $mock->getRawValue());

        $processor = $this->getProcessor(["process"]);
        $processor->expects($this->any())->method("process")->with($mock)->willReturn(["transformed"]);

        $this->assertSame($mock, $mock->validate($processor));

        $this->assertSame(["transformed"], $mock->getValue());
        $this->assertSame("value", $mock->getRawValue());

        $this->assertSame($mock, $mock->validate($processor));

        $this->assertSame(["transformed"], $mock->getValue());
        $this->assertSame("value", $mock->getRawValue());
    }


    /**
     *
     * @return QueryParameter|MockObject
     */
    protected function getQueryParameter(array $methods, array $args): MockObject
    {
        return $this->getMockForAbstractClass(
            QueryParameter::class,
            $args,
            '',
            true,
            true,
            true,
            $methods
        );
    }

    /**
     *
     * @return QueryParameterProcessor|MockObject
     */
    protected function getProcessor(array $methods): MockObject
    {
        return $this->getMockForAbstractClass(
            QueryParameterProcessor::class,
            [],
            '',
            true,
            true,
            true,
            $methods
        );
    }
}

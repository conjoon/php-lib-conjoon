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

use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\ParameterList;
use Conjoon\Http\Query\Query;
use Tests\TestCase;

/**
 * Tests QueryParameter.
 *
 */
class QueryTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testGetParameterBag()
    {
        $query = $this->createMockForAbstract(Query::class, ["getParameters"]);

        $parameterList = new ParameterList();
        $parameterList[] = new Parameter("fields[MessageItem]", "a,b,c");
        $parameterList[] = new Parameter("sort", "subject,-date");

        $query->expects($this->once())->method("getAllParameters")->willReturn($parameterList);

        $parameterBag = $query->getParameterBag();

        $this->assertSame(
            [
            "fields[MessageItem]" => "a,b,c",
            "sort" => "subject,-date"
            ],
            $parameterBag->toJson()
        );
    }
}
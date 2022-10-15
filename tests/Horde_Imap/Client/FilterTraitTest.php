<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2021-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Tests\Conjoon\Horde_Imap\Client;

use Conjoon\Filter\Filter;
use Conjoon\Horde_Imap\Client\FilterTrait;
use Conjoon\Math\Expression\FunctionalExpression;
use Conjoon\Math\Expression\LogicalExpression;
use Conjoon\Math\Expression\RelationalExpression;
use Conjoon\Math\Value;
use Conjoon\Math\VariableName;
use Tests\TestCase;

/**
 * Class FilterTraitTest
 * @package Tests\Conjoon\Horde_Imap\Client
 */
class FilterTraitTest extends TestCase
{
    /**
     * Tests getSearchQueryFromFilter
     */
    public function testGetSearchQueryFromFilter()
    {
        $translator = $this->getMockedTrait();

        $tests = [
            [
                "input" => new Filter(
                    LogicalExpression::OR(
                        RelationalExpression::EQ(
                            VariableName::make("RECENT"),
                            Value::make(true)
                        ),
                        RelationalExpression::GE(
                            VariableName::make("ID"),
                            Value::make(1000)
                        )
                    )
                ),
                "output" => "OR (UID 1000:*) (RECENT)"
            ], [
                "input" => new Filter(
                    RelationalExpression::EQ(
                        VariableName::make("RECENT"),
                        Value::make(true)
                    )
                ),
                "output" => "RECENT"
            ], [
                "input" => new Filter(
                    RelationalExpression::GE(
                        VariableName::make("UID"),
                        Value::make(1000)
                    )
                ),
                "output" => "UID 1000:*"
            ], [
                "input" =>  new Filter(
                    LogicalExpression::OR(
                        RelationalExpression::EQ(
                            VariableName::make("RECENT"),
                            Value::make(true)
                        ),
                        FunctionalExpression::IN(
                            VariableName::make("UID"),
                            Value::make(1000),
                            Value::make(1001)
                        )
                    )
                ),
                "output" => "OR (UID 1000:1001) (RECENT)"
            ]
        ];


        foreach ($tests as $test) {
            $this->assertSame(
                $test["output"],
                $translator->getSearchQueryFromFilter($test["input"])->__toString()
            );
        }
    }


    /**
     * @return __anonymous@1559
     */
    public function getMockedTrait()
    {
        return new class (){
            use FilterTrait;
        };
    }
}

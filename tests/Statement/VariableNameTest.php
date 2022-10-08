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

namespace Tests\Conjoon\Statement;

use Conjoon\Statement\VariableName;
use Conjoon\Statement\Operand;
use Tests\JsonableTestTrait;
use Tests\StringableTestTrait;
use Tests\TestCase;

/**
 * Tests VariableName.
 */
class VariableNameTest extends TestCase
{
    use JsonableTestTrait;
    use StringableTestTrait;

// ---------------------
//    Tests
// ---------------------

    /**
     * Tests class
     */
    public function testConstructor()
    {
        $variableName = new VariableName("name");
        $this->assertInstanceOf(Operand::class, $variableName);

        $this->assertSame("name", $variableName->getValue());


        $variableName = VariableName::make("name");
        $this->assertInstanceOf(VariableName::class, $variableName);
        $this->assertSame("name", $variableName->getValue());
    }


    /**
     * Tests toArray()
     */
    public function testToArray()
    {
        $variableName = new VariableName("name");

        $this->assertSame(["name"], $variableName->toArray());
    }


    /**
     * Tests toJson()
     */
    public function testToJson()
    {
        $variableName = new VariableName("name");
        $this->runToJsonTest($variableName);
    }


    /**
     * Tests toString()
     */
    public function testToString()
    {
        $variableName = new VariableName("name");
        $this->assertSame("name", $variableName->toString());

        $this->runToStringTest(VariableName::class);
    }
}

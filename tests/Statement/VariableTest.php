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

namespace Tests\Conjoon\Core\Data;

use Conjoon\Statement\Variable;
use Conjoon\Statement\VariableName;
use Conjoon\Statement\Value;
use Conjoon\Statement\Operand;
use Tests\JsonableTestTrait;
use Tests\StringableTestTrait;
use Tests\TestCase;

/**
 * Tests Variable.
 */
class VariableTest extends TestCase
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
        $n = new VariableName("name");
        $v = new Value("value");

        $variable = new Variable($n, $v);
        $this->assertInstanceOf(Operand::class, $variable);

        $this->assertSame($n, $variable->getName());
        $this->assertSame($v, $variable->getValue());

        $variable = Variable::make("name", "value");
        $this->assertInstanceOf(Variable::class, $variable);

        $this->assertSame("name", $variable->getName()->getValue());
        $this->assertSame("value", $variable->getValue()->getValue());
    }


    /**
     * Tests toArray()
     */
    public function testToArray()
    {
        $n = new VariableName("name");
        $v = new Value("value");

        $variable = new Variable($n, $v);

        $this->assertSame([$n->toArray(), $v->toArray()], $variable->toArray());
    }


    /**
     * Tests toJson()
     */
    public function testToJson()
    {
        $n = new VariableName("name");
        $v = new Value("value");

        $variable = new Variable($n, $v);
        $this->runToJsonTest($variable);
    }


    /**
     * Tests toString()
     */
    public function testToString()
    {
        $n = new VariableName("name");
        $v = new Value("value");

        $variable = new Variable($n, $v);
        $this->assertSame($n->toString() . ":" . $v->toString(), $variable->toString());

        $this->runToStringTest($this->createMockForAbstract(Variable::class, ["toString"], [$n, $v]));
    }
}

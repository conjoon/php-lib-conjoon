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

namespace Tests\Conjoon\Http\Query\Validation;

use Conjoon\Core\Exception\UnexpectedTypeException;
use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\Core\Validation\Validator as BaseValidator;
use Conjoon\Http\Query\Exception\UnexpectedQueryException;
use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\ParameterList;
use Conjoon\Http\Query\Query;
use Conjoon\Http\Query\Validation\ParameterRule;
use Conjoon\Http\Query\Validation\QueryRule;
use Conjoon\Http\Query\Validation\Validator;
use stdClass;
use Tests\TestCase;

/**
 * Tests Validator.
 */
class ValidatorTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $validator = $this->createMockForAbstract(Validator::class);
        $this->assertInstanceOf(BaseValidator::class, $validator);
    }


    /**
     * test supports()
     */
    public function testSupports()
    {
        $validator = $this->createMockForAbstract(Validator::class);

        $this->assertTrue(
            $validator->supports(
                $this->getMockBuilder(Query::class)->disableOriginalConstructor()->getMock()
            )
        );
    }


    /**
     * tests validate() with parameter not of type JsonApi\Query
     */
    public function testValidateWithUnexpectedTypeException()
    {
        $this->expectException(UnexpectedTypeException::class);
        $validator = $this->createMockForAbstract(Validator::class);

        $validator->validate(new stdClass(), new ValidationErrors());
    }


    /**
     * tests validate() with supports() returning false
     */
    public function testValidateWithUnexpectedQueryException()
    {
        $query =  $this->getMockBuilder(Query::class)->disableOriginalConstructor()->getMock();
        $this->expectException(UnexpectedQueryException::class);
        $validator = $this->createMockForAbstract(Validator::class, ["supports"]);
        $validator->expects($this->once())->method("supports")->with($query)->willReturn(false);

        $validator->validate($query, new ValidationErrors());
    }



    /**
     * tests validate()
     */
    public function testValidate()
    {
        $query = $this->createMockForAbstract(Query::class, ["getAllParameters"]);
        $errors = new ValidationErrors();

        $parameters = new ParameterList();
        $parameters[] = new Parameter("include", "MessageItem");
        $parameters[] = new Parameter("fields[MessageItem]", "subject");

        $query->expects($this->once())
              ->method("getAllParameters")
              ->willReturn($parameters);

        $queryRules = [
            $this->createMockForAbstract(QueryRule::class, ["isValid"]),
            $this->createMockForAbstract(QueryRule::class, ["isValid"])
        ];

        $parameterRules = [
            $this->createMockForAbstract(ParameterRule::class, ["shouldValidateParameter", "isValid"]),
            $this->createMockForAbstract(ParameterRule::class, ["shouldValidateParameter", "isValid"])
        ];

        $validator = $this->getMockBuilder(Validator::class)
                          ->onlyMethods(["getQueryRules", "getParameterRules"])
                          ->getMock();

        $validator->expects($this->once())->method("getQueryRules")->with($query)->willReturn($queryRules);
        $validator->expects($this->once())->method("getParameterRules")->with($query)->willReturn($parameterRules);

        foreach ($queryRules as $queryRule) {
            $queryRule->expects($this->once())->method("isValid")->with($query, $errors)->willReturn(true);
        }

        foreach ($parameterRules as $parameterRule) {
                $parameterRule->expects($this->exactly(2))
                              ->method("shouldValidateParameter")
                              ->withConsecutive([$parameters[0]], [$parameters[1]])
                              ->willReturnOnConsecutiveCalls(true, false);

                $parameterRule->expects($this->exactly(1))
                              ->method("isValid")
                              ->with($parameters[0], $errors)
                              ->willReturn(true);
        }

        $validator->validate($query, $errors);
    }
}

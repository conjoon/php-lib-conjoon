<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2022-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Tests\Conjoon\Web\Validation;

use Conjoon\Core\Exception\UnexpectedTypeException;
use Conjoon\Data\ParameterList;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Data\Validation\Validator as BaseValidator;
use Conjoon\Net\Uri\Component\Query;
use Conjoon\Net\Uri\Component\Query\Parameter;
use Conjoon\Web\Validation\Exception\UnexpectedQueryException;
use Conjoon\Web\Validation\Parameter\ParameterRule;
use Conjoon\Web\Validation\Parameter\ParameterRuleList;
use Conjoon\Web\Validation\Query\QueryRule;
use Conjoon\Web\Validation\Query\QueryRuleList;
use Conjoon\Web\Validation\QueryValidator;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Tests\TestCase;

/**
 * Tests QueryValidator.
 */
class QueryValidatorTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass(): void
    {
        $validator = $this->createMockForAbstract(QueryValidator::class);
        $this->assertInstanceOf(BaseValidator::class, $validator);
    }


    /**
     * test supports()
     */
    public function testSupports(): void
    {
        /**
         * @var MockObject&QueryValidator $validator
         */
        $validator = $this->createMockForAbstract(QueryValidator::class);

        $this->assertTrue(
            $validator->supports(
                $this->getMockBuilder(Query::class)->disableOriginalConstructor()->getMock()
            )
        );
    }


    /**
     * tests validate() with parameter not of type JsonApi\Query\Query
     */
    public function testValidateWithUnexpectedTypeException(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        /**
         * @var MockObject&QueryValidator $validator
         */
        $validator = $this->createMockForAbstract(QueryValidator::class);

        $validator->validate(new stdClass(), new ValidationErrors());
    }


    /**
     * tests validate() with supports() returning false
     */
    public function testValidateWithUnexpectedQueryException(): void
    {
        $query =  $this->getMockBuilder(Query::class)->disableOriginalConstructor()->getMock();
        $this->expectException(UnexpectedQueryException::class);
        /**
         * @var MockObject&QueryValidator $validator
         */
        $validator = $this->createMockForAbstract(QueryValidator::class, ["supports"]);
        $validator->expects($this->once())->method("supports")->with($query)->willReturn(false);

        $validator->validate($query, new ValidationErrors());
    }



    /**
     * tests validate()
     */
    public function testValidate(): void
    {
        $query = $this->createMockForAbstract(Query::class, ["getAllParameters"]);
        $errors = new ValidationErrors();

        $parameters = new ParameterList();
        $parameters[] = new Parameter("include", "MessageItem");
        $parameters[] = new Parameter("fields[MessageItem]", "subject");

        $query->expects($this->once())
            ->method("getAllParameters")
            ->willReturn($parameters);

        $queryRules = new QueryRuleList();
        /**
         * @var MockObject&QueryRule $rule1
         */
        $rule1 = $this->createMockForAbstract(QueryRule::class, ["supports", "validate"]);
        $queryRules[] = $rule1;
        /**
         * @var MockObject&QueryRule $rule2
         */
        $rule2 = $this->createMockForAbstract(QueryRule::class, ["supports", "validate"]);
        $queryRules[] = $rule2;

        $parameterRules = new ParameterRuleList();
        /**
         * @var MockObject&ParameterRule $parameterRule1
         */
        $parameterRule1 = $this->createMockForAbstract(ParameterRule::class, [
            "supports", "validate"
        ]);
        $parameterRules[] = $parameterRule1;

        /**
         * @var MockObject&ParameterRule $parameterRule2
         */
        $parameterRule2 = $this->createMockForAbstract(ParameterRule::class, [
            "supports", "validate"
        ]);
        $parameterRules[] = $parameterRule2;

        /**
         * @var MockObject&QueryValidator $validator
         */
        $validator = $this->createMockForAbstract(QueryValidator::class, [
            "getQueryRules",
            "getParameterRules"
        ]);

        $validator->expects($this->once())->method("getQueryRules")->with($query)->willReturn($queryRules);
        $validator->expects($this->once())->method("getParameterRules")->with($query)->willReturn($parameterRules);

        foreach ($queryRules as $queryRule) {
            /**
             * @var MockObject&QueryRule $queryRule
             */
            $queryRule->expects($this->once())->method("validate")->with($query, $errors)->willReturn(true);
            $queryRule->expects($this->once())->method("supports")->with($query)->willReturn(true);
        }

        foreach ($parameterRules as $parameterRule) {
            /**
             * @var MockObject&ParameterRule $parameterRule
             */
            $parameterRule->expects($this->exactly(3))
                ->method("supports")
                ->withConsecutive([$parameters[0]], [$parameters[0]], [$parameters[1]])
                ->willReturnOnConsecutiveCalls(true, true, false);

            $parameterRule->expects($this->exactly(1))
                ->method("validate")
                ->with($parameters[0], $errors)
                ->willReturn(true);
        }

        $validator->validate($query, $errors);
    }
}

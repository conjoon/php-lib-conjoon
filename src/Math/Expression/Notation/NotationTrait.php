<?php


declare(strict_types=1);

namespace Conjoon\Math\Expression\Notation;

use Conjoon\Data\Validation\ValidationError;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Math\Expression\Expression;
use Conjoon\Math\Expression\LogicalExpression;
use Conjoon\Math\Expression\Operator\FunctionalOperator;
use Conjoon\Math\Expression\Operator\LogicalOperator;
use Conjoon\Math\Expression\Operator\RelationalOperator;
use Conjoon\Math\InvalidOperandException;
use Conjoon\Math\Operand;
use Conjoon\Math\OperandList;
use Conjoon\Math\Value;
use Conjoon\Net\Uri\Component\Query\Parameter;
use Conjoon\Web\Validation\Parameter\Rule\JsonEncodedRule;
use RuntimeException;

trait NotationTrait
{

    /**
     * Returns true if the passed argument is a valid operator.
     *
     * @param string $value
     * @return bool
     */
    public function isValidOperator(string $value): bool
    {
        return in_array($value, $this->getRelationalOperators()) ||
            $this->isLogicalOperator($value) ||
            in_array($value, $this->getFunctions());
    }



    /**
     * Returns true if the submitted argument is a logical operator.
     *
     * @param string $value
     *
     * @return bool
     */
    public function isLogicalOperator(string $value): bool
    {
        return in_array($value, $this->getLogicalOperators());
    }


    /**
     * Returns the list of relational operators available.
     *
     * @return string[]
     */
    public function getRelationalOperators(): array
    {
        return array_merge(array_map(fn($enum) => $enum->value, RelationalOperator::cases()), ["="]);
    }


    /**
     * Returns the list of logical operators supported.
     *
     * @return string[]
     */
    public function getLogicalOperators(): array
    {
        return [LogicalOperator::OR->value, "OR", LogicalOperator::AND->value, "AND"];
    }


    /**
     * Returns the list of functions supported.
     *
     * @return string[]
     */
    public function getFunctions(): array
    {
        return array_map(fn($enum) => $enum->value, FunctionalOperator::cases());
    }

}


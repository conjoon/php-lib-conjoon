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

namespace Conjoon\JsonApi\Query\Validation\Parameter;

use Conjoon\Data\Validation\ValidationError;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Math\Expression\Notation\NotationTrait;
use Conjoon\Math\Expression\Operator\FunctionalOperator;
use Conjoon\Math\Expression\Operator\LogicalOperator;
use Conjoon\Math\Expression\Operator\RelationalOperator;
use Conjoon\Net\Uri\Component\Query\Parameter;
use Conjoon\Web\Validation\Parameter\Rule\JsonEncodedRule;

/**
 * This parameter rule supports validation of filters provided as json encoded objects containing
 * filter rules in a polish notation, where the keys are operators and/or functions, and the values
 * subsequent filters or filter arguments.
 *
 * The following filter query parameter requests the resource collection to only contain the resource data
 * with the id "1":
 * ?filter={"=": {"id": 1]}
 *
 * The following requests data marked as "recent", or data marked as "unseen" or with an id >= 1657:
 * ?filter={"OR":[{"=":{"recent":true}}, "OR": [{"=": {"unseen": true}}, {">=": {"id":1657}}]}
 *
 * This rule applies for parameters named "filter"
 */
class PnFilterRule extends JsonEncodedRule
{
    use NotationTrait;

    /**
     * @var array<int, string>
     */
    protected array $attributes;


    /**
     * Constructor.
     *
     * @param array<int, string> $attributes The list of valid attributes this rule considers.
     */
    public function __construct(array $attributes)
    {
        parent::__construct("filter");

        $this->attributes = $attributes;
    }

    /**
     * @inheritdoc
     */
    protected function validate(Parameter $parameter, ValidationErrors $errors): bool
    {
        if (!parent::validate($parameter, $errors)) {
            return false;
        }

        $value = json_decode($parameter->getValue(), true);

        $parse = function ($value) use (&$parse) {


            foreach ($value as $operator => $filter) {
                if (!$this->isValidOperator($operator)) {
                    return "\"$operator\" is not a valid operator";
                }

                if (!$this->isLogicalOperator($operator)) {
                    $attributes = array_keys($filter);
                    $diff = array_values(array_diff($attributes, $this->getAttributes()));
                    if (count($diff) > 0) {
                        return "\"$operator\" needs a valid attribute argument, received \"" . implode("\", \"", $diff) . "\"";
                    }
                    continue;
                }

                //{"OR" : [{"==": ...}, {">=" : ...}}]
                if (count($filter) <= 1) {
                    return "Logical operator \"$operator\" " .
                        "expects at least 2 operands, " . count($filter) . " given";
                }

                foreach ($filter as $operand) {
                    $res = $parse($operand);
                    if (is_string($res)) {
                        return $res;
                    }
                }
            }

            return true;
        };

        $error = $parse($value);

        if (is_string($error)) {
            $errors[] = new ValidationError(
                $parameter,
                $error,
                400
            );

            return false;
        }

        return true;
    }


    /**
     * Returns all the attribute names this rule considers. The list of rules is dynamic and must be
     * specified with the constructor.
     *
     * @return array<int, string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}

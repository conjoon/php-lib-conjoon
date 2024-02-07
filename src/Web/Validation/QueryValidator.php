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

namespace Conjoon\Web\Validation;

use Conjoon\Core\Exception\UnexpectedTypeException;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Data\Validation\Validator as BaseValidator;
use Conjoon\Net\Uri\Component\Query;
use Conjoon\Web\Validation\Exception\UnexpectedQueryException;
use Conjoon\Web\Validation\Parameter\ParameterRuleList;
use Conjoon\Web\Validation\Query\QueryRuleList;

/**
 * Class for validating queries that must be checked for validity with QueryRules and ParameterRules.
 */
abstract class QueryValidator implements BaseValidator
{
    /**
     * Return true if this class supports validating the specified Query, otherwise false.
     *
     * @param Query $query
     * @return bool
     */
    public function supports(Query $query): bool
    {
        return true;
    }


    /**
     * @inheritdoc
     *
     * @throws UnexpectedTypeException|UnexpectedQueryException if $obj is not an instance of Query,
     * or if the return value for the supplied $obj returns false
     */
    public function validate(object $obj, ValidationErrors $errors): void
    {
        if (!$obj instanceof Query) {
            throw new UnexpectedTypeException(
                "expected target object for validation to be instance of " . Query::class
            );
        }

        if (!$this->supports($obj)) {
            throw new UnexpectedQueryException(
                "query is not supported by this validator"
            );
        }

        foreach ($this->getQueryRules($obj) as $queryRule) {
            $queryRule->isValid($obj, $errors);
        }

        $parameters = $obj->getAllParameters();

        foreach ($this->getParameterRules($obj) as $parameterRule) {
            $parameters->map(
                fn ($parameter) => $parameterRule->supports($parameter)
                                   ? $parameterRule->isValid($parameter, $errors)
                                   : null
            );
        }
    }


    /**
     * Returns the ParameterRules for the specified Query.
     *
     * @param Query $query
     *
     * @return ParameterRuleList
     */
    abstract public function getParameterRules(Query $query): ParameterRuleList;


    /**
     * Returns the QueryRules for the specified Query.
     *
     * @param Query $query
     *
     * @return QueryRuleList
     */
    abstract public function getQueryRules(Query $query): QueryRuleList;


    /**
     * Returns a list of parameter names that must be available with the Query.
     *
     * @param Query $query
     * @return array<int, string>
     */
    abstract public function getRequiredParameterNames(Query $query): array;


    /**
     * Returns a list of allowed parameter names that must appear exclusively with the
     * query.
     *
     * @return array<int, string>
     */
    abstract public function getAllowedParameterNames(Query $query): array;


    /**
     * Return true if the specified $parameterName is an allowed parameter-name for the
     * specified $query, otherwise false.
     *
     * @param string $name
     * @param Query $query
     * @return bool
     */
    public function isAllowedParameterName(string $parameterName, Query $query): bool {
        return in_array($parameterName, $this->getAllowedParameterNames($query));
    }
}

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

namespace Conjoon\JsonApi\Validation;

use Conjoon\Http\Query\Exception\UnexpectedQueryParameterException;
use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\Validation\Query\ParameterNamesInListQueryRule;
use Conjoon\Http\Query\Validation\Validator as HttpQueryValidator;
use Conjoon\Http\Query\Query as HttpQuery;
use Conjoon\JsonApi\Query\Query;
use Conjoon\JsonApi\Validation\Parameter\FieldsetRule;
use Conjoon\JsonApi\Validation\Parameter\IncludeRule;

/**
 * Class for validating queries that must be checked for validity according to JSON:API
 * specifications.
 */
class Validator extends HttpQueryValidator
{
    /**
     * @inheritdoc
     */
    public function supports(HttpQuery $query): bool
    {
        return $query instanceof Query;
    }


    /**
     * Returns the ParameterRules for the specified Query.
     *
     * @param Query $query
     *
     * @return array
     */
    public function getParameterRules(HttpQuery $query): array
    {
        $resourceTarget = $query->getResourceTarget();

        $include  = $query->getParameter("include");
        $includes = $include
            ? $this->unfoldInclude($include)
            : [];

        return [
            new IncludeRule($resourceTarget->getAllRelationshipPaths()),
            new FieldsetRule(
                $resourceTarget->getAllRelationshipResourceDescriptions(true),
                $includes
            ),
        ];
    }


    /**
     * Returns the QueryRules for the specified Query.
     *
     * @param Query $query
     *
     * @return array
     */
    public function getQueryRules(HttpQuery $query): array
    {
        return [
            new ParameterNamesInListQueryRule($this->getValidParameterNamesForQuery($query))
        ];
    }


    /**
     * Returns all the parameter names including possible fieldsets based on the Reource Target of the Query.
     *
     * @param Query $query
     *
     * @return array
     */
    public function getValidParameterNamesForQuery(Query $query): array
    {
        $resourceTarget = $query->getResourceTarget();
        $exp = ["include", "fields[{$resourceTarget->getType()}]"];
        $list = $resourceTarget->getAllRelationshipPaths(false);
        foreach ($list as $type) {
            $exp[] = "fields[$type]";
        }

        return $exp;
    }


    /**
     * Unfolds possible dot notated types available with includes and returns it as
     * an array with all resource types as unique values in the resulting array.
     *
     * @example
     *   $this->unfoldInclude(
     *        new Parameter("include",
     *        "MailFolder.MailAccount,MailFolder"));  // ["MailAccount", "MailFolder"]
     *
     *
     * @param Parameter $parameter
     *
     * @return array
     *
     * @throws UnexpectedQueryParameterException
     */
    protected function unfoldInclude(Parameter $parameter): array
    {
        if ($parameter->getName() !== "include") {
            throw new UnexpectedQueryParameterException(
                "parameter passed does not seem to be the \"include\" parameter"
            );
        }

        $includes = $parameter->getValue() ? explode(",", $parameter->getValue()) : null;

        if (!$includes) {
            return [];
        }


        $res = [];
        foreach ($includes as $include) {
            $res = array_merge($res, explode(".", $include));
        }

        return array_values(array_unique($res));
    }
}

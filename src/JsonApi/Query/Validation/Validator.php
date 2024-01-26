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

namespace Conjoon\JsonApi\Query\Validation;

use Conjoon\JsonApi\Query\Query;
use Conjoon\JsonApi\Query\Validation\Parameter\FieldsetRule;
use Conjoon\JsonApi\Query\Validation\Parameter\IncludeRule;
use Conjoon\Net\Uri\Component\Query as HttpQuery;
use Conjoon\Net\Uri\Component\Query\Parameter;
use Conjoon\Web\Validation\Exception\UnexpectedQueryParameterException;
use Conjoon\Web\Validation\Parameter\ParameterRuleList;
use Conjoon\Web\Validation\Query\QueryRuleList;
use Conjoon\Web\Validation\Query\Rule\OnlyParameterNamesRule;
use Conjoon\Web\Validation\Query\Rule\RequiredParameterNamesRule;
use Conjoon\Web\Validation\QueryValidator as HttpQueryValidator;

/**
 * Class for validating queries that target resource objects. Queries are checked for
 * validity according to JSON:API specifications.
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
     * @return ParameterRuleList
     */
    public function getParameterRules(HttpQuery $query): ParameterRuleList
    {
        $resourceTarget = $query->getResourceTarget();

        $include  = $query->getParameter("include");
        $includes = $include
            ? $this->unfoldInclude($include)
            : [];

        $list = new ParameterRuleList();
        $list[] = new IncludeRule($resourceTarget->getAllRelationshipPaths());
        $list[] = new FieldsetRule(
            $resourceTarget->getAllRelationshipResourceDescriptions(true),
            $includes
        );

        return $list;
    }


    /**
     * Returns the QueryRules for the specified Query.
     *
     * @param Query $query
     *
     * @return QueryRuleList
     */
    public function getQueryRules(HttpQuery $query): QueryRuleList
    {
        $list = new QueryRuleList();
        $list[] = new OnlyParameterNamesRule($this->getAllowedParameterNames($query));
        $list[] = new RequiredParameterNamesRule($this->getRequiredParameterNames($query));

        return $list;
    }


    /**
     * Returns all the parameter names including possible fieldsets based on the Resource Target of the Query.
     *
     * @param Query $query
     *
     * @return array<int, string>
     */
    public function getAllowedParameterNames(HttpQuery $query): array
    {
        $resourceTarget = $query->getResourceTarget();

        $exp = [];
        $list = $this->unfoldRelationships($resourceTarget->getAllRelationshipPaths(true));

        foreach ($list as $type) {
            $exp[] = "fields[$type]";
        }
        return array_merge(["include"], $exp);
    }


    /**
     * @inheritdoc
     *
     * @return array<int, string>
     */
    public function getRequiredParameterNames(HttpQuery $query): array
    {
        return [];
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
     * @return array<int, string>
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

        return $this->unfoldRelationships($includes);
    }


    /**
     * Returns an array with all the entries in $relationships reduced to their TYPE-names.
     *
     * @examnple
     *    $res = $this->unfoldRelationships(["MailFolder", "MessageItem.MailFolder"]);
     *    // $res: ["MailFolder", "MessageItem"]
     *
     *
     * @param array<int, string> $relationships
     * @return array<int, string>
     */
    protected function unfoldRelationships(array $relationships): array
    {
        $res = [];

        foreach ($relationships as $relationship) {
            $res = array_merge($res, explode(".", $relationship));
        }

        return array_values(array_unique($res));
    }
}
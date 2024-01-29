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

namespace Conjoon\JsonApi\Query\Validation;

use Conjoon\JsonApi\Query\JsonApiQuery as JsonApiQuery;
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
class JsonApiQueryValidator extends HttpQueryValidator
{
    /**
     * @inheritdoc
     */
    public function supports(HttpQuery $query): bool
    {
        return $query instanceof JsonApiQuery;
    }


    /**
     * Returns the ParameterRules for the specified Query.
     *
     * @param HttpQuery $query
     *
     * @return ParameterRuleList
     */
    public function getParameterRules(HttpQuery $query): ParameterRuleList
    {
        /**
         * @type JsonApiQuery $query
         */
        $resourceTarget = $query->getResourceDescription();

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
     * @param HttpQuery $query
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
     * @param HttpQuery $query
     *
     * @return array<int, string>
     */
    public function getAllowedParameterNames(HttpQuery $query): array
    {
        /**
         * @type JsonApiQuery $query
         */
        $resourceTarget = $query->getResourceDescription();

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

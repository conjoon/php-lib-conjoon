<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Conjoon\MailClient\JsonApi\Query;

use Conjoon\JsonApi\Query\Query;
use Conjoon\JsonApi\Query\Validation\CollectionQueryValidator;
use Conjoon\Web\Validation\Query\Rule\ExclusiveGroupKeyRule;
use Conjoon\Web\Validation\Query\QueryRuleList;
use Conjoon\JsonApi\Extensions\Query\Validation\Parameter\RelfieldRule;
use Conjoon\Net\Uri\Component\Query as HttpQuery;
use Conjoon\Web\Validation\Parameter\ParameterRuleList;

/**
 * Query Validator for MailFolder collection requests.
 *
 */
class MailAccountListQueryValidator extends CollectionQueryValidator
{
    public function getParameterRules(HttpQuery $query): ParameterRuleList
    {
        /**
         * @type Query $query
         */
        $resourceTarget = $query->getResourceDescription();

        $list = parent::getParameterRules($query);
        $list[] = new RelfieldRule(
            $resourceTarget->getAllRelationshipResourceDescriptions(true), [], false
        );

        return $list;
    }


    public function getAllowedParameterNames(HttpQuery $query): array
    {
        $names = parent::getAllowedParameterNames($query);
        foreach ($names as $param) {
            if (str_starts_with($param, "fields[")) {
                $res[] = "relfield:$param";
            } else {
                $res[] = $param;
            }
        }

        return $res;
    }


    public function getQueryRules(HttpQuery $query): QueryRuleList
    {
        $list = parent::getQueryRules($query);
        $list[] = new ExclusiveGroupKeyRule(["fields", "relfield:fields"]);

        return $list;
    }
}

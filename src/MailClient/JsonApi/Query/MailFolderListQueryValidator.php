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

use Conjoon\Web\Validation\Parameter\Rule\IntegerValueRule;
use Conjoon\Web\Validation\Query\Rule\ExclusiveGroupKeyRule;
use Conjoon\Web\Validation\Query\QueryRuleList;
use Conjoon\JsonApi\Extensions\Query\Validation\Parameter\RelfieldRule;
use Conjoon\JsonApi\Query\Validation\CollectionQueryValidator;
use Conjoon\Net\Uri\Component\Query as HttpQuery;
use Conjoon\JsonApi\Query\Validation\Parameter\PnFilterRule;
use Conjoon\Web\Validation\Parameter\ParameterRuleList;

/**
 * Query Validator for MailFolder collection requests.
 *
 */
class MailFolderListQueryValidator extends CollectionQueryValidator
{
    public function getParameterRules(HttpQuery $query): ParameterRuleList
    {
        $resourceTarget = $query->getResourceTarget();

        $include  = $query->getParameter("include");
        $includes = $include
            ? $this->unfoldInclude($include)
            : [];


        $list = parent::getParameterRules($query);
        $list[] = new RelfieldRule(
            $resourceTarget->getAllRelationshipResourceDescriptions(true),
            $includes,
            false
        );
        $list[] = new PnFilterRule($this->getAvailableFields($resourceTarget));

        return $list;
    }


    public function getAllowedParameterNames(HttpQuery $query): array
    {
        $names = parent::getAllowedParameterNames($query);
        $res = ["filter"];
        foreach ($names as $param) {
            if (substr($param, 0, 7) === "fields[") {
                $res[] = "relfield:$param";
            } else {
                $res[] = $param;
            }
        }

        return $res;
    }


    public function getQueryRules(HttpQuery $query): QueryRuleList
    {
        $list = parent::getQueryRules();
        $list[] = new ExclusiveGroupKeyRule(["fields", "relfield:fields"]);

        return $list;
    }
}

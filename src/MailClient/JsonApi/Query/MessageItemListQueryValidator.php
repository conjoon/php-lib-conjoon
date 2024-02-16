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
use Conjoon\Net\Uri\Component\Query as HttpQuery;
use Conjoon\Web\Validation\Parameter\ParameterRuleList;
use Conjoon\Web\Validation\Parameter\Rule\IntegerValueRule;
use Conjoon\Web\Validation\Parameter\Rule\JsonEncodedRule;

/**
 * Query Validator for MessageItemList requests.
 *
 */
class MessageItemListQueryValidator extends BaseListQueryValidator
{

    /**
     * @Override
     */
    public function getRequiredParameterNames(HttpQuery $query): array
    {
        return ["page[start]", "page[limit]"];
    }


    public function getParameterRules(HttpQuery $query): ParameterRuleList
    {
        $list = parent::getParameterRules($query);
        $list[] = new JsonEncodedRule("options[MessageBody]");
        $list[] = new IntegerValueRule("page[limit]", ">=", 1);
        $list[] = new IntegerValueRule("page[start]", ">=", 0);
        return $list;
    }


    /**
     * @Override
     */
    public function getAllowedParameterNames(HttpQuery $query): array
    {
        /**
         * @type Query $query
         */
        $resourceTarget = $query->getResourceDescription();

        $res = [];
        $res[] = "fields[$resourceTarget]";
        $res[] = "relfield:fields[$resourceTarget]";
        $res[] = "fields[MessageBody]";
        $res[] = "relfield:fields[MessageBody]";
        $res[] = "sort";
        $res[] = "include";
        $res[] = "page[start]";
        $res[] = "page[limit]";
        $res[] = "options[MessageBody]";

        return $res;
    }
}

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
use Conjoon\JsonApi\Query\Validation\Parameter\PnFilterRule;
use Conjoon\Web\Validation\Parameter\ParameterRuleList;

/**
 * Query Validator for MailFolder collection requests.
 *
 */
class MailFolderListQueryValidator extends BaseListQueryValidator
{
    public function getParameterRules(HttpQuery $query): ParameterRuleList
    {
        /**
         * @type Query $query
         */
        $resourceTarget = $query->getResourceDescription();

        $list = parent::getParameterRules($query);
        $list[] = new PnFilterRule($this->getAvailableFields($resourceTarget));

        return $list;
    }


    /**
     * @Override
     */
    public function getAllowedParameterNames(HttpQuery $query): array
    {
        $names = parent::getAllowedParameterNames($query);

        $res = array_filter($names, fn($name) => !in_array($name, ["sort", "include"]));
        $res[] = "filter";
        $res[] = "options[MailFolder]";
        return $res;
    }
}

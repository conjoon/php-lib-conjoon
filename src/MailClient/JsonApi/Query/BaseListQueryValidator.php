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

use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\Data\Resource\ResourceDescriptionList;
use Conjoon\JsonApi\Query\Query;
use Conjoon\JsonApi\Query\Query as JsonApiQuery;
use Conjoon\Web\Validation\Query\Rule\ExclusiveGroupKeyRule;
use Conjoon\Web\Validation\Query\QueryRuleList;
use Conjoon\JsonApi\Extensions\Query\Validation\Parameter\RelfieldRule;
use Conjoon\JsonApi\Query\Validation\CollectionQueryValidator;
use Conjoon\Net\Uri\Component\Query as HttpQuery;
use Conjoon\Web\Validation\Parameter\ParameterRuleList;


abstract class BaseListQueryValidator extends CollectionQueryValidator
{
    public function getParameterRules(HttpQuery $query): ParameterRuleList
    {
        /**
         * @type Query $query
         */
        $resourceTarget = $query->getResourceDescription();
        $self = new ResourceDescriptionList();
        $self[] = $resourceTarget;

        $list = parent::getParameterRules($query);
        $list[] = new RelfieldRule(
            $this->isAllowedParameterName("include", $query)
                ? $resourceTarget->getAllRelationshipResourceDescriptions(true)
                : $self,
            [$resourceTarget],
            false
        );

        return $list;
    }

    /**
     * @Override
     */
    public function getAllowedParameterNames(HttpQuery $query): array
    {

        /**
         * @type JsonApiQuery $query
         */
        $resourceDescription = $query->getResourceDescription();

        return array_merge(
            parent::getAllowedParameterNames($query),
            ["relfield:fields[{$resourceDescription}]"]
        );
    }


    public function getQueryRules(HttpQuery $query): QueryRuleList
    {
        $list = parent::getQueryRules($query);
        $list[] = new ExclusiveGroupKeyRule(["fields", "relfield:fields"]);

        return $list;
    }
}

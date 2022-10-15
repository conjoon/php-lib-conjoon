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

use Conjoon\Http\Query\Validation\Parameter\ParameterRuleList;
use Conjoon\Http\Query\Validation\Parameter\ValuesInWhitelistRule;
use Conjoon\Http\Query\Query as HttpQuery;
use Conjoon\JsonApi\Query\Query;
use Conjoon\Core\Resource\ObjectDescription;

/**
 * Class for validating queries that target resource collections(!) according to JSON:API
 * specifications.
 * A collectionValidator validates queries based on the Validator class.
 * Additionally, the "sort"-query parameter will be
 * considered for validation.
 */
class CollectionValidator extends Validator
{
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

        $sort = $query->getParameter("sort");
        $sort = $sort
            ? $this->getAvailableSortFields($resourceTarget)
            : [];

        $list = parent::getParameterRules($query);
        $list[] = new ValuesInWhitelistRule("sort", $sort);

        return $list;
    }


    /**
     * Returns all the parameter names for a collection query, including sorting parameter options.
     *
     * @param HttpQuery $query
     *
     * @return array
     */
    public function getAllowedParameterNames(HttpQuery $query): array
    {
        return array_merge(
            parent::getAllowedParameterNames($query),
            ["sort"]
        );
    }


    /**
     * Returns all available fields for the specified $resourceTarget to be used with the sort query parameter.
     * The list returned will be an array containing the field names, and dot-separated field names where the
     * first part of the name is the type of the resource target.
     *
     * @param ObjectDescription $resourceTarget
     * @return array
     */
    protected function getAvailableSortFields(ObjectDescription $resourceTarget): array
    {
        $res = $this->getAvailableFields($resourceTarget);

        return array_merge($res, array_map(fn ($field) => "-$field", $res));
    }


    /**
     * Returns all available fields for the specified $resourceTarget, along(!) with its relationships.
     * The list returned will be an array containing the field names, and dot-separated field names where the
     * first part of the name is the type of the resource target, or the related resource target.
     *
     * @param ObjectDescription $resourceTarget
     * @return array
     */
    protected function getAvailableFields(ObjectDescription $resourceTarget): array
    {
        $res = $resourceTarget->getFields();

        $descriptions = $resourceTarget->getAllRelationshipResourceDescriptions(true);

        foreach ($descriptions as $entity) {
            $fields = $entity->getFields();
            $type   = $entity->getType();
            $res = array_merge($res, array_map(fn ($field) => "$type.$field", $fields));
        }

        return $res;
    }
}

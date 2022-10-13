<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2021-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Conjoon\Horde\Mail\Client\Imap;

use Conjoon\Filter\Filter;
use Conjoon\Math\Expression\Expression;
use Horde_Imap_Client;
use Horde_Imap_Client_Ids;
use Horde_Imap_Client_Search_Query;

/**
 * Trait for building SearchQueries based on the available Filter.
 * Filter configurations are treated as OR-queries.
 *
 * @example
 *
 *    $filter = new Filter(
 *        LogicalExpression::OR(
 *            RelationalExpression::EQ(
 *                VariableName::make("RECENT"),
 *                Value::make(true)
 *            ),
 *            RelationalExpression::GE(
 *                VariableName::make("UID"),
 *                Value::make(1000)
 *            )
 *        )
 *    );
 *
 *    $this->getSearchQueryFromFilterTrait($filter)->__toString(); // "OR (UID 1000:*) (RECENT)"
 *
 * Trait FilterTrait
 * @package Conjoon\Horde\Mail\Client\Imap
 */
trait FilterTrait
{
    /**
     * Looks up filter information from the passed Filter and creates a
     * Horde_Imap_Client_Search_Query from it.
     *
     * @param Filter $filter
     *
     * @return Horde_Imap_Client_Search_Query
     */
    public function getSearchQueryFromFilter(Filter $filter): Horde_Imap_Client_Search_Query
    {
        $searchQuery = new Horde_Imap_Client_Search_Query();

        $clientSearches = $this->transformFilter($filter->getExpression());
        $searchQuery->orSearch($clientSearches);

        return $searchQuery;
    }


    /**
     * Transforms the submitted expression into an array of Horde_Imap_Client_Search_Query
     *
     * @param Expression $filter
     *
     * @return array
     */
    protected function transformFilter(Expression $filter): array
    {
        $clientSearches = [];

        $operator = $filter->getOperator()->toString();
        $operands = $filter->getOperands();

        if ($operator === "||") {
            foreach ($operands as $operand) {
                $clientSearches = array_merge($clientSearches, $this->transformFilter($operand));
            }
        } elseif ($operator === "&&") {
            throw new \RuntimeException("AND not supported");
        }

        $property = $filter->getOperands()[0]->toString();
        $value    = $filter->getOperands()[1];

        if ($property === "UID") {
            if ($operator === ">=") {
                $value    = array_slice($filter->getOperands()->toArray(), 1);
                $filterId = implode(":", $value) . ":*";
                $latestQuery = new Horde_Imap_Client_Search_Query();
                $latestQuery->ids(new Horde_Imap_Client_Ids([$filterId]));
                $clientSearches[] = $latestQuery;
            } elseif ($operator === "IN") {
                $value    = array_slice($filter->getOperands()->toArray(), 1);
                $latestQuery = new Horde_Imap_Client_Search_Query();
                $latestQuery->ids(new Horde_Imap_Client_Ids($value));
                $clientSearches[] = $latestQuery;
            }
        }
        if ($property === "RECENT" && $operator === "==" && $value->getValue() === true) {
            $recentQuery = new Horde_Imap_Client_Search_Query();
            $recentQuery->flag(Horde_Imap_Client::FLAG_RECENT, true);
            $clientSearches[] = $recentQuery;
        }

        return $clientSearches;
    }
}

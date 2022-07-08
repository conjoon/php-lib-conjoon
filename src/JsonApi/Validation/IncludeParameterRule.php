<?php

/**
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

use Conjoon\Core\Data\ArrayUtil;
use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\Validation\ValueInWhitelistRule;

/**
 * Class providing functionality for making sure a query's include parameter contains only valid
 * resource objects according to the relationships available with the resource target
 * of the query.
 *
 * Validates include-parameter values in the form of Resource[.RelationShipResource]
 */
class IncludeParameterRule extends ValueInWhitelistRule
{
    /**
     * @inheritdoc
     */
    public function __construct(array $whitelist)
    {
        parent::__construct("include", $whitelist);
    }


    /**
     * @inheritdoc
     */
    protected function isParameterValueValid(Parameter $parameter, $whitelist): bool
    {
        $source = $this->merge($this->parse($parameter->getValue()));
        return ArrayUtil::hasOnly(array_flip($source), $whitelist);
    }


    /**
     * Returns an array with all "include"-values as specified in the string, each
     * relationship to include separated with a ",".
     * Returns an empty array for an array string.
     *
     * @param string $value
     * @return array
     */
    protected function parse(string $value): array
    {
        if ($value === "") {
            return [];
        }
        return explode(",", $value);
    }


    /**
     * Merges all the includes into the greatest common divisors possible.
     * The JSON:API specifies that "intermediate resources in a multi-part
     * path must be returned along with the leaf nodes" (@see https://jsonapi.org/format/#fetching-includes).
     * A client may send a request with a query parameter "include" in the form of
     * <pre>
     * include=MailFolder,MailFolder.MailAccount
     * </pre>
     * which is a valid value for the "include" query parameter. However, due to the specification
     * quoted above, the "MailFolder"-value is redundant, so the includes can be merged from
     * ["MailFolder", "MailFolder.MailAccount"] into ["MailFolder.MailAccount"].
     *
     * @param array $includes
     * @return array
     */
    protected function merge(array $includes): array
    {
        $result = array_values($includes);
        foreach ($includes as $include) {
            $result = array_filter(
                $result,
                fn ($item) => $item === $include || strpos($include, $item) !== 0 || strpos($item, $include) === 0
            );
        }

        return array_values($result);
    }


    /**
     * Unfolds possible dot notated types available with includes and returns it as
     * an array.
     *
     * @example
     *    $this->unfold(["MailFolder.MailAccount", "MailFolder"]); // ["MailAccount", "MailFolder"]
     *
     * @param array $includes
     *
     * @return array
     */
    protected function unfold(array $includes): array
    {
        $res = [];
        foreach ($includes as $include) {
            $res = array_merge($res, explode(".", $include));
        }

        return array_values(array_unique($res));
    }
}

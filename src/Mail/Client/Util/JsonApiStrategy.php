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

namespace Conjoon\Mail\Client\Util;

use Conjoon\Util\JsonStrategy;
use Conjoon\Util\Arrayable;

/**
 * Class JsonApiStrategy
 * @package Conjoon\Mail\Client\Util;
 */
class JsonApiStrategy implements JsonStrategy
{
    /**
     * Transforms the $data to a format that matches the JSON:API specifications by considering
     * attributes and relationships.
     *
     * @param Arrayable $source Expects $source to be an instance of jsonable
     *
     * @return array
     *
     * @see https://jsonapi.org
     */
    public function toJson(Arrayable $source): array
    {
        $data = $source->toArray();

        $types = [
            "mailFolderId"  => "MailFolder",
            "mailAccountId" => "MailAccount",
        ];

        $result = [
            "attributes" => []
        ];

        foreach ($data as $field => $value) {
            if (in_array($field, ["id", "type"])) {
                $result[$field] = $value;
                continue;
            }

            if (in_array($field, ["mailFolderId", "mailAccountId"])) {
                if (!isset($result["relationships"])) {
                    $result["relationships"] = [];
                }
                $result["relationships"]["$types[$field]s"] = [
                    "data" => [
                        "id"   => $value,
                        "type" => $types[$field]
                    ]
                ];
                continue;
            }

            $result["attributes"][$field] = $value;
        }

        return $result;
    }
}

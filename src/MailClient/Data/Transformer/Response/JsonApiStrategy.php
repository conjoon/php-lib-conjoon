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

namespace Conjoon\MailClient\Data\Transformer\Response;

use Conjoon\Data\Validation\ValidationError;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\JsonProblem\AbstractProblem;
use Conjoon\Core\Contract\JsonStrategy;
use Conjoon\Core\Contract\Arrayable;
use Conjoon\Core\AbstractList;

/**
 * Class JsonApiStrategy
 * @package Conjoon\MailClient\Data\Protocol\Transformer\Response;
 */
class JsonApiStrategy implements JsonStrategy
{
    /**
     * Transforms the $data to a format that matches the JSON:API specifications by considering
     * attributes and relationships.
     * Makes sure child elements are properly considered where applicable, e.g. for MailFolders.
     *
     * @param Arrayable $source Expects $source to be an instance of Jsonable
     *
     * @return array
     *
     * @see https://jsonapi.org
     */
    public function toJson(Arrayable $source): array
    {
        if ($source instanceof AbstractProblem) {
            return $this->transformFromError($source->toArray());
        }

        if ($source instanceof ValidationError) {
            return $source->toArray();
        }

        if ($source instanceof ValidationErrors) {
            return $this->fromValidationErrors($source);
        }

        if ($source instanceof AbstractList) {
            return $this->fromAbstractList($source);
        }

        $data = $source->toArray();
        if (!isset($data["type"])) {
            return $data;
        }

        return $this->transform($data);
    }


    /**
     * Implementation for the json strategy.
     *
     * @param array $data
     *
     * @return array|array[]
     */
    protected function transform(array $data): array
    {
        $types = [
            "mailFolderId"  => "MailFolder",
            "mailAccountId" => "MailAccount",
        ];

        $result = [
            "attributes" => []
        ];


        $createRelationship = function ($key) use ($types, $data, &$result) {
            if (!isset($data[$key])) {
                return false;
            }
            if (!isset($result["relationships"])) {
                $result["relationships"] = [];
            }
            $result["relationships"]["$types[$key]"] = [
                "data" => [
                    "id"   => $data[$key],
                    "type" => $types[$key]
                ]
            ];
            return true;
        };

        // look up MailFolderFirst, then create this relationship.
        // nested MailAccount will not be reflected if a MailFolder is
        // available, since the resource linkage to the owning MailAccount will
        // be done given the MailFolder
        $createRelationship("mailFolderId") ||
        $createRelationship("mailAccountId");

        foreach ($data as $field => $value) {
            if (in_array($field, ["id", "type"])) {
                $result[$field] = $value;
                continue;
            }

            if (in_array($field, ["mailFolderId", "mailAccountId"])) {
                continue;
            }

            $result["attributes"][$field] = $value;
        }

        // if type is MailFolder, recurse into child mail folders
        $attributes = &$result["attributes"];
        if (isset($attributes["data"]) && $result["type"] === "MailFolder") {
            $children = [];
            foreach ($attributes["data"] as $node) {
                $children[] = $this->transform($node);
            }
            $attributes["data"] = $children;
        }

        return $result;
    }


    /**
     * Makes sure the abstract list is properly transformed into its JSON representative by forwarding THIS
     * strategy to each of its list entries.
     *
     * @param AbstractList $source
     *
     * @return array
     */
    public function fromAbstractList(AbstractList $source): array
    {
        $data = [];

        foreach ($source as $item) {
            $data[] = $item->toJson($this);
        }

        return $data;
    }


    /**
     * Makes sure the ValidationErrors-list is properly transformed into its JSON representative.
     *
     * @param ValidationErrors $source
     *
     * @return array
     */
    public function fromValidationErrors(ValidationErrors $source): array
    {
        $data = [];

        foreach ($source as $item) {
            $data[] = $item->toJson($this);
        }

        return [
            "errors" => $data
        ];
    }


    /**
     * Transforms from a Problem-representative to an JSON:API error object.
     *
     * @param array $data
     *
     * @return array
     *
     * @see https://jsonapi.org/format/#errors
     */
    protected function transformFromError(array $data): array
    {
        $problem = [
            "title" => $data["title"] ?? null,
            "status" => $data["status"] ?? null,
            "detail" => $data["detail"] ?? null,
            "links" => isset($data["type"]) && $data["type"] !== "about:blank" ? [
                "about" => $data["type"]
            ] : null,
            "meta" => isset($data["instance"]) ? [
                "instance" => $data["instance"]
            ] : null
        ];

        return array_filter($problem, fn ($v) => !empty($v));
    }
}

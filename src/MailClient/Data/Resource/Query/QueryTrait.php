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

namespace Conjoon\MailClient\Data\Resource\Query;

use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\Data\Resource\ResourceDescriptionList;
use Conjoon\Data\Sort\SortDirection;
use Conjoon\Data\Sort\SortInfo;
use Conjoon\Data\Sort\SortInfoList;
use Conjoon\MailClient\Data\Resource\MessageBodyDescription;


trait QueryTrait
{

    private function getFieldsForResource(ResourceDescription $desc): array
    {
        $defaultFields = $desc->getDefaultFields();

        $relfields = $this->{"relfield:fields[{$desc}]"};
        $fields    = $this->{"fields[{$desc}]"};

        if (!$relfields) {
            if (!$this->has("fields[{$desc}]")) {
                return $defaultFields;
            }
            return $fields ? explode(",", $fields) : [];
        }

        $relfields = explode(",", $relfields);

        foreach ($relfields as $relfield) {
            $prefix    = substr($relfield, 0, 1);
            $fieldName = substr($relfield, 1);

            if ($prefix === "-") {
                $defaultFields = array_filter($defaultFields, fn ($field) => $field !== $fieldName);
            } else {
                if (!in_array($fieldName, $defaultFields)) {
                    $defaultFields[] = $fieldName;
                }
            }
        }

        return $defaultFields;
    }

    public function getInclude(): ResourceDescriptionList
    {
        $list = new ResourceDescriptionList();
        $resources = $this->{"include"} ? explode(",", $this->{"include"}) : [];

        $rel = $this->getResourceDescription()->getAllRelationshipResourceDescriptions(false);

        foreach ($rel as $resource) {
            if (in_array($resource->getType(), $resources)) {
                $list[] = $resource;
            }
        }

        return $list;
    }

    /**
     * @param ResourceDescription::class|null $className
     * @return bool
     */
    protected function includes(string $className): bool {
        $list = $this->getInclude();

        /**
         * @type $className ResourceDescription::class
         */
        return $list->findBy(fn($item) => $item === $className::getInstance()) !== null;
    }

    /**
     * @param ResourceDescription::class|null $className
     * @return array|null
     */
    public function getFields(string $className = null): ?array
    {
        if ($className === null) {
            $className = get_class($this->getResourceDescription());
        }

        if ($className === get_class($this->getResourceDescription()) ||
            $this->includes($className)) {
            return  $this->getFieldsForResource($className::getInstance());
        }

        return null;
    }

    protected function availableOptions(string $className): ?array {

        if (!$this->includes($className)) {
            return null;
        }

        /**
         * @type $className ResourceDescription::class
         */
        $resource = $className::getInstance();

        return $this->has("options[" . $resource->getType() . "]")
            ? json_decode($this->{"options[" . $resource->getType() . "]"}, true)
            : null;
    }

}
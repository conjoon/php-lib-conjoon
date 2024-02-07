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

namespace Conjoon\JsonApi\Extensions\Query\Validation\Parameter;

use Conjoon\Data\Resource\ResourceDescriptionList;
use Conjoon\Data\Validation\ValidationError;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\JsonApi\Query\Validation\Parameter\FieldsetRule;
use Conjoon\Net\Uri\Component\Query\Parameter;

/**
 * An extension on sparse fieldset as specified with https://conjoon.org/json-api/ext/relfield.
 * Supports group parameters with the name "relfield:fields".
 * This rule supports wildcards.
 *
 * Empty fieldsets in the form of "relfield:fields[TYPE]=" are treated as valid.
 */
class RelfieldRule extends FieldsetRule
{
    /**
     * @var bool
     */
    protected bool $wildcardEnabled;

    /**
     * Constructor.
     * Overrides parent constructor by allowing for passing a boolean $wildcardEnabled for indicating
     * this server supports wildcards.
     *
     * @param ResourceDescriptionList $resourceDescriptionList
     * @param array<int, string> $includes
     * @param bool $wildcardEnabled
     */
    public function __construct(
        ResourceDescriptionList $resourceDescriptionList,
        array $includes,
        bool $wildcardEnabled = false
    ) {
        parent::__construct($resourceDescriptionList, $includes);
        $this->wildcardEnabled = $wildcardEnabled;
    }


    /**
     * @inheritdoc
     */
    public function supports(object $obj): bool
    {
        return ($obj instanceof Parameter) &&
            $this->isGroupParameter($obj) &&
            $this->getGroupName($obj) === "relfield:fields";
    }


    /**
     * @inheritdoc
     */
    protected function validateValue(Parameter $parameter, ValidationErrors $errors): bool
    {
        $value          = $parameter->getValue();
        if ($value === "") {
            return true;
        }

        $name   = $parameter->getName();
        $type   = $this->getGroupKey($parameter);
        $resourceFields = ($type ? $this->getFields($type) : []) ?? [];

        $fields = explode(",", $value);

        $wildcards = array_filter($fields, fn ($field) => $field === "*");
        if (count($wildcards) > 1) {
            $errors[] = new ValidationError(
                $parameter,
                "The relfield-specification does not allow more than one wildcard for \"$name\"",
                400
            );
            return false;
        }

        $wildcardFound = in_array("*", $fields);
        if ($wildcardFound && !$this->wildcardEnabled) {
            $errors[] = new ValidationError(
                $parameter,
                "This server does not support wildcards with the relfield-extension",
                400
            );
            return false;
        }


        // remove wildcards
        $fields = array_filter($fields, fn ($field) => $field !== "*");


        // sanitize fields and strip prefixes where applicable
        $fields = array_map(
            fn ($field) =>
                in_array(substr($field, 0, 1), ["-"])
                    ? substr($field, 1)
                    : $field,
            $fields
        );

        return $this->checkFieldList($fields, $resourceFields, $parameter, $errors);
    }
}

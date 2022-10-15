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

namespace Conjoon\JsonApi\Extensions\Query\Validation\Parameter;

use Conjoon\Core\Validation\ValidationError;
use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\Http\Query\Parameter;
use Conjoon\JsonApi\Query\Validation\Parameter\FieldsetRule;
use Conjoon\Core\Data\Resource\ObjectDescriptionList;

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
     * @param ObjectDescriptionList $resourceDescriptionList
     * @param array $includes
     * @param bool $wildcardEnabled
     */
    public function __construct(
        ObjectDescriptionList $resourceDescriptionList,
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
        $name           = $parameter->getName();
        $type           = $this->getGroupKey($parameter);
        $resourceFields = $this->getFields($type);

        if ($value === "" || $value === null) {
            return true;
        }

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


        // check if any field is not prefixed with a +/-
        $invalidFields = array_filter($fields, fn ($field) => !in_array(substr($field, 0, 1), ["+", "-"]));
        if (count($invalidFields) !== 0) {
            $errors[] = new ValidationError(
                $parameter,
                "The relfield-specification expects each field to be prefixed with a \"+\" or a \"-\""
            );
            return false;
        }

        // sanitize fields and strip prefixes where applicable
        $fields = array_map(
            fn ($field) =>
                in_array(substr($field, 0, 1), ["+", "-"])
                    ? substr($field, 1)
                    : $field,
            $fields
        );

        return $this->checkFieldList($fields, $resourceFields, $parameter, $errors);
    }
}

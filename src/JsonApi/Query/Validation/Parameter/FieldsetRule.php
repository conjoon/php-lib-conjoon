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

namespace Conjoon\JsonApi\Query\Validation\Parameter;

use Conjoon\Core\Validation\ValidationError;
use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\ParameterTrait;
use Conjoon\Http\Query\Validation\Parameter\ParameterRule;
use Conjoon\Core\Data\Resource\ObjectDescriptionList;

/**
 * Validates fieldset parameters of the type "fields[TYPE]=field,field2,field3" given the
 * ObjectDescriptionList containing ObjectDesciptions that can be identified with TYPE.
 * The fields found as values for "fields[TYPE]" are compared against the list of fields
 * defined with the ObjectDescriptions. TYPE itself must be found in any of the $includes this class was configured
 * with.
 *
 * This validator supports all parameters given the name "fields[TYPE]", whereas "TYPE" represents
 * an ObjectDescription's getType().
 * Validating will fail
 *   - if the fields specified with "fields[TYPE]" contain entries that do not exist
 *      in the list of fields defined with the ObjectDescription
 *   - if TYPE is not found in the list of $includes this class was configured with

 * Empty fieldsets in the form of "fields[TYPE]=" are treated as valid.
 *
 */
class FieldsetRule extends ParameterRule
{
    use ParameterTrait;

    /**
     * @var ObjectDescriptionList
     */
    protected ObjectDescriptionList $resourceDescriptionList;

    /**
     * @var array $includes
     */
    protected array $includes;

    /**
     * Constructor.
     * Creates a new instance of this rule configured with the available resource object descriptions
     * and the includes the rule should consider.
     *
     * @param ObjectDescriptionList $resourceDescriptionList
     * @param array $includes An array with all resource object types requested, against which a parameter's
     * fieldset must be validated
     */
    public function __construct(ObjectDescriptionList $resourceDescriptionList, array $includes)
    {
        $this->resourceDescriptionList = $resourceDescriptionList;
        $this->includes                = $includes;
    }


    /**
     * Returns the ObjectDescriptionList this rule uses.
     *
     * @return ObjectDescriptionList
     */
    public function getResourceObjectDescriptions(): ObjectDescriptionList
    {
        return $this->resourceDescriptionList;
    }


    /**
     * Returns the list of includes this rule was configures with
     *
     * @return array
     */
    public function getIncludes(): array
    {
        return $this->includes;
    }


    /**
     * @inheritdoc
     */
    public function supports(object $obj): bool
    {
        return parent::supports($obj) &&
               $this->isGroupParameter($obj) &&
               $this->getGroupName($obj) === "fields";
    }


    /**
     * @inehritdoc
     */
    protected function validate(Parameter $parameter, ValidationErrors $errors): bool
    {
        $name = $parameter->getName();
        $type = $this->getGroupKey($parameter);

        if (!$this->validateIncludes($parameter, $errors)) {
            return false;
        }

        $resourceFields = $this->getFields($type);

        if (!$resourceFields) {
            $errors[] = new ValidationError(
                $parameter,
                "Cannot find fields for parameter \"$name\"",
                400
            );
            return false;
        }


        if (!$this->validateValue($parameter, $errors)) {
            return false;
        }

        return true;
    }


    /**
     * Returns the field for the ObjectDescription identified by $type.
     *
     * @param string $type
     *
     * @return array|null
     */
    protected function getFields(string $type): ?array
    {
        return $this->getResourceObjectDescriptions()
                    ->findBy(fn ($resource) => $resource->getType() === $type)?->getFields();
    }


    /**
     * Validates the key for the specified fieldset group parameter.
     *
     * @param $parameter
     * @param ValidationErrors $errors
     *
     * @return bool
     */
    protected function validateIncludes(Parameter $parameter, ValidationErrors $errors): bool
    {
        $includes = $this->getIncludes();
        $type = $this->getGroupKey($parameter);

        if (!in_array($type, $includes)) {
            $errors[] = new ValidationError(
                $parameter,
                "The requested type \"$type\" cannot be found in the list of includes, which was \"" .
                implode("\", \"", $includes) .
                "\"",
                400
            );
            return false;
        }

        return true;
    }


    /**
     * Validates the value of the fieldset parameter.
     *
     * @param Parameter $parameter
     * @param ValidationErrors $errors
     *
     * @return bool
     */
    protected function validateValue(Parameter $parameter, ValidationErrors $errors): bool
    {
        $value = $parameter->getValue();
        $type = $this->getGroupKey($parameter);
        $resourceFields = $this->getFields($type);

        if ($value === "" || $value === null) {
            return true;
        }

        $fields = explode(",", $value);

        return $this->checkFieldList($fields, $resourceFields, $parameter, $errors);
    }


    /**
     * Helper function for validating that the $actualFields appear in $allowedFields.
     *
     * @param array $actualFields
     * @param array $allowedFields
     * @param Parameter $parameter
     * @param ValidationErrors $errors
     *
     * @return array
     */
    protected function checkFieldList(
        array $actualFields,
        array $allowedFields,
        Parameter $parameter,
        ValidationErrors $errors
    ): bool {
        $spill = array_diff($actualFields, $allowedFields);

        if (count($spill) === 0) {
            return true;
        }

        $errors[] = new ValidationError(
            $parameter,
            "The following fields for \"" . $parameter->getName() . "\" cannot be found " .
            "in the resource object: \"" .
            implode("\", \"", $spill) .
            "\"",
            400
        );

        return false;
    }
}

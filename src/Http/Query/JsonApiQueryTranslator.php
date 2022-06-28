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

namespace Conjoon\Http\Query;

use Conjoon\Core\ParameterBag;
use Conjoon\Http\Resource\ResourceObjectDescription;
use Conjoon\Http\Resource\ResourceObjectDescriptionList;
use Conjoon\Util\ArrayUtil;
use Illuminate\Http\Request;

/**
 * Abstract class providing functionality when working with requests queries
 * according to JSON:API-specification, including functionality for considering
 * requests for compound documents and sparse fieldsets.
 *
 * Class JsonApiQueryTranslator
 * @package Conjoon\Http\Query
 */
abstract class JsonApiQueryTranslator extends QueryTranslator
{
    /**
     * Returns the description for the resource object targeted by this query.
     *
     * @return ResourceObjectDescription
     */
    abstract public function getResourceTarget(): ResourceObjectDescription;


    /**
     * Queries relationships and builds up the final lists of fields requested, then
     * stores them in the ParamaterBag.
     *
     * @param ParameterBag $bag
     *
     * @return ParameterBag
     *
     * @throws InvalidQueryException if the value of "include" lists relationships that
     * are not available
     *
     * @see getIncludes()
     */
    protected function getFieldsets(ParameterBag $bag): ParameterBag
    {
        $bag = $this->getIncludes($bag);

        $types = array_merge(
            [$this->getResourceTarget()->getType()],
            $bag->getString("include") ? explode(",", $bag->getString("include")) : []
        );

        $bag->fields = [];

        foreach ($types as $type) {
            $fieldsetQueryValue = $bag->getString("fields[$type]");

            $fields = $this->parseFields($fieldsetQueryValue, $type);
            $fieldOptions = $this->extractFieldOptions($bag, $type);

            $bag->fields = array_merge(
                $bag->fields,
                [
                    $type => $this->mapConfigToFields(
                        $fields,
                        $fieldOptions,
                        $type
                    )]
            );


            unset($bag->{"fields[$type]"});
        }

        return $bag;
    }


    /**
     * Fills the bag with all possible includes represented by the relationships of the
     * resource object targeted by this query.
     *
     * @param ParameterBag $bag
     *
     * @return ParameterBag
     *
     * @throws InvalidQueryException if the value of "include" lists relationships that
     * are not available
     */
    protected function getIncludes(ParameterBag $bag): ParameterBag
    {
        $relList = $this->getRelatedResourceTargetTypes();

        if ($bag->include) {
            $includes = array_flip(explode(",", $bag->getString("include")));

            if (!ArrayUtil::hasOnly($includes, $relList)) {
                throw new InvalidQueryException(
                    "parameter \"include\" must only contain one of " .
                    implode(", ", $relList) . ", was: " . $bag->getString("include")
                );
            }
        }

        return $bag;
    }



    /**
     * Checks if there are fields which are not part of the fields defined for the
     * entity the translator should process the parameters for.
     *
     * @param $received
     * @param string $type
     *
     * @return array
     */
    protected function hasOnlyAllowedFields($received, $type): array
    {
        return array_diff($received, $this->getFields($type));
    }


    /**
     * Returns all fields the specified entity exposes.
     *
     * @param string $type The entity type for which the fields should be returned.
     *
     * @return string[]
     *
     * @see ResourceObjectDescription#getType
     */
    protected function getFields(string $type): array
    {

        return $this->getFieldsOrDefaultFields($type, false);
    }


    /**
     * Returns the list of default fields defined with the resource object with the
     * specified $type.
     *
     * @param string $type
     * @return array
     *
     * @see ResourceObjectDescription#getType
     */
    protected function getDefaultFields(string $type): array
    {
        return $this->getFieldsOrDefaultFields($type, true);
    }


    /**
     * Returns the list of default fields or fields depending on the second argument
     * passed to this method.
     *
     * @param string $type
     * @param bool $default true to call getDefaultFields(), otherwise getFields() is
     * calles.
     * @return array
     *
     * @see ResourceObjectDescription#getType
     */
    private function getFieldsOrDefaultFields(string $type, bool $default): array
    {
        $resourceObjects = $this->getRelatedResourceTargets(true);

        foreach ($resourceObjects as $rel) {
            if ($rel->getType() === $type) {
                return $default === true ? $rel->getDefaultFields() : $rel->getFields();
            }
        }

        return [];
    }

    /**
     * @inheritdocs
     */
    protected function extractParameters($parameterResource): array
    {
        if (!($parameterResource instanceof Request)) {
            throw new InvalidParameterResourceException(
                "Expected \"parameterResource\" to be instance of {Illuminate::class}"
            );
        }
        return $parameterResource->only($this->getExpectedParameters());
    }


    /**
     * Returns the parameters this class expects and understands for
     * translating.
     *
     * @return array
     */
    protected function getExpectedParameters(): array
    {
        $exp = [];
        $list = $this->getRelatedResourceTargetTypes(true);
        foreach ($list as $type) {
            $exp[] = "fields[$type]";
        }

        return $exp;
    }


    /**
     * Returns the getType() value of all of the relationships available for this
     * resource target, along with all children of the resource object represented by
     * an relationship.
     *
     * @param bool $withResourceTarget If true, returns the list including the resource
     * target of *this* QueryTranslator
     *
     * @return array
     */
    protected function getRelatedResourceTargetTypes(bool $withResourceTarget = false): array
    {
        $list = $this->getRelatedResourceTargets($withResourceTarget);

        return $list->map(fn($rel) => $rel->getType());
    }


    /**
     * Returns all resource object description available with all relationships spawning
     * from the resource object target for this query and its related resources.
     *
     * @param bool $withResourceTarget If true, returns the list including the resource
     * target of *this* QueryTranslator
     *
     *
     * @return ResourceObjectDescriptionList
     */
    protected function getRelatedResourceTargets($withResourceTarget = false): ResourceObjectDescriptionList
    {

        $list = new ResourceObjectDescriptionList();

        if ($withResourceTarget === true) {
            $list[] = $this->getResourceTarget();
        }

        $traverse = function ($resourceObject) use ($list, &$traverse) {

            $t = $resourceObject->getRelationships();

            foreach ($t as $rel) {
                $list[] = $rel;
                $traverse($rel);
            }
        };

        $traverse($this->getResourceTarget());

        return $list;
    }


    /**
     * Parses and builds up the field list. Returns the fields defined with getFields()
     * if the passed $queryValue was NULL.
     * Returns an empty array for an empty string passed to the query parameter (i.e.
     * ?fields[TYPE]=&someOtherParam=2
     *
     * @param string|null $queryValue
     * @param string $type
     *
     * @return string[]
     *
     * @throws InvalidQueryException if any field was specified with the query paramater
     * that is not in the list of allowed fields for the resource object with the type $type..
     */
    protected function parseFields(?string $queryValue, string $type): array
    {
        // must be set before parseFields is called to make sure it does not fall back to default
        if ($queryValue === null) {
            return $this->getFields($type);
        }
        $queryFields = $queryValue === "" ? [] : explode(",", $queryValue);
        $fields = $queryFields;

        if (count($queryFields) === 0) {
            return $fields;
        }

        if (in_array("*", $queryFields) !== false) {
            $excludes  = array_filter($queryFields, fn ($item) => $item !== "*");
            // re-index
            $fields = array_values(
                array_filter($this->getFields($type), fn ($item) => !in_array($item, $excludes))
            );
        } else {
            $notAllowed = $this->hasOnlyAllowedFields($queryFields ?? [], $type);
            if (!empty($notAllowed)) {
                throw new InvalidQueryException(
                    "parameter \"fields[$type]\" has unknown entries: " . implode(",", $notAllowed)
                );
            }
        }

        return $fields;
    }


    /**
     * Extracts additional options for fields configured with the query and available
     * in the ParameterBag, if any.
     * This is a template method that simply returns an empty array. Any QueryTranslator that
     * requires options with fieldsets passed with the query should implement the method.
     *
     * @param ParameterBag $bag
     * @param string $type The resource object for which the method was called.
     *
     * @return array
     */
    protected function extractFieldOptions(ParameterBag $bag, string $type): array
    {
        return [];
    }


    /**
     * Maps required configurations to passed fields not available in the target
     * entity.
     * This is a template method that simply returns the value of getDefaultFields()
     * for the resource object with the specified $type.
     *
     * @param array $fields
     * @param array $fieldOptions
     * @param string $type
     *

     * @return array
     */
    protected function mapConfigToFields(array $fields, array $fieldOptions, string $type): array
    {
        return $this->getDefaultFields($type);
    }
}

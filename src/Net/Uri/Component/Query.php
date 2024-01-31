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

namespace Conjoon\Net\Uri\Component;

use Conjoon\Data\ParameterBag;
use Conjoon\Data\ParameterList;
use Conjoon\Error\ErrorSource;
use Conjoon\Net\Uri\Component\Query\Parameter;
use Conjoon\Net\Uri\Component\Query\ParameterTrait;

/**
 * Interface used to control Http-Queries.
 */
class Query implements ErrorSource
{
    use ParameterTrait;

    /**
     * @var string
     */
    private string $queryString;

    /**
     * @var array<string, array<string, string>|string>
     */
    private array $parsedParameters = [];

    /**
     * @var array<string, ?Parameter>
     */
    private array $parameterList = [];


    /**
     * @var ParameterList|null
     */
    private ?ParameterList $list = null;


    /**
     * Constructor.
     *
     * @param string|null $queryString
     */
    public function __construct(string $queryString = null)
    {
        if ($queryString) {
            parse_str($queryString, $this->parsedParameters);
        }

        $this->queryString = $queryString ?? "";
    }

    /**
     * Gets the parameter from this Query. Returns null if no parameter with the
     * name exists with this query.
     *
     * @param string $name
     * @return Parameter|null
     */
    public function getParameter(string $name): ?Parameter
    {
        if (array_key_exists($name, $this->parameterList)) {
            return $this->parameterList[$name];
        }
        $exists = array_key_exists($name, $this->parsedParameters) ;

        if ($exists && !is_array($this->parsedParameters[$name])) {
            $this->parameterList[$name] = new Parameter($name, $this->parsedParameters[$name]);
            return $this->parameterList[$name];
        }

        if ($this->isGroupParameter($name)) {
            $groupName = $this->getGroupName($name);

            if ($groupName) {
                $groups = $this->parsedParameters[$groupName];

                if (is_array($groups)) {
                    foreach ($groups as $iName => $value) {
                        if ($name === $groupName . "[" . $iName . "]") {
                            $this->parameterList[$name] = new Parameter($name, $value);
                            break;
                        }
                    }
                }
            }
        }

        if (!array_key_exists($name, $this->parameterList)) {
            $this->parameterList[$name] = null;
        }

        return $this->parameterList[$name];
    }


    /**
     * Returns a ParameterList containing all the parameters of this query.
     *
     * @return ParameterList
     */
    public function getAllParameters(): ParameterList
    {
        if ($this->list) {
            return $this->list;
        }

        $list = new ParameterList();

        foreach ($this->parsedParameters as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $groupName => $groupValue) {
                    $fieldName = $key . "[" . $groupName . "]";
                    $currentParam = $this->getParameter($fieldName);
                    if ($currentParam) {
                        $list[] = $currentParam;
                    }
                }
            } else {
                $currentParam = $this->getParameter($key);
                if ($currentParam) {
                    $list[] = $currentParam;
                }
            }
        }

        $this->list = $list;
        return $list;
    }


    /**
     * Returns an array containing all available parameter names of this query.
     * Returns an empty array if no parameters are available.
     *
     * @return array<int|string, mixed>
     */
    public function getAllParameterNames(): array
    {
        return $this->getAllParameters()->map(fn ($param) => $param->getName());
    }


    /**
     * Returns a ParameterBag containing all the data from this parameters.
     *
     * @return ParameterBag
     */
    public function getParameterBag(): ParameterBag
    {
        return new ParameterBag($this->getAllParameters()->toArray());
    }


    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->__toString();
    }

    /**
     * @inheritdoc
     */
    public function getSource(): object
    {
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->getAllParameters()->toArray();
    }

    /**
     * @Override
     */
    public function __toString(): string
    {
        return $this->queryString;
    }
}

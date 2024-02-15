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

namespace Conjoon\Web\Validation\Parameter\Rule;

use Conjoon\Net\Uri\Component\Query\Parameter;
use Conjoon\Web\Validation\Parameter\ParameterRule;

/**
 * NamedParameterRule provides extended functionality for supports() base implementation
 * by returning true if, and only if ParameterRule's supports() returns true and if the name of
 * the parameter being validated is in the list of parameter names this rule was configured with.
 *
 */
abstract class NamedParameterRule extends ParameterRule
{
    /**
     * @var string|array<int, string>
     */
    protected string|array $parameterName;

    /**
     * Constructor.
     *
     * @param string|array<int, string> $parameterName The name of the parameter for which this rule should be
     * applied, or an array of names so the rule can be re-used with multiple parameters
     */
    public function __construct(string|array $parameterName)
    {
        $this->parameterName = $parameterName;
    }


    /**
     * @inheritdoc
     */
    public function supports(object $obj): bool
    {
        $names = is_array($this->parameterName) ? $this->parameterName : [$this->parameterName];

        /**
         * @var Parameter $obj
         */
        return parent::supports($obj) &&
            in_array($obj->getName(), $names);
    }


    public function getParameterName(): string|array
    {
        return $this->parameterName;
    }
}

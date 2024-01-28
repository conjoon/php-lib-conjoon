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

namespace Tests\Conjoon\Data\Resource;

use Conjoon\Data\Resource\ObjectDescription;
use Conjoon\Data\Resource\ObjectDescriptionList;

/**
 * Test class loaded with tests for Locator
 */
class TestResourceObjectDescription extends ObjectDescription
{
    protected ?int $one ;
    protected ?int $two;
    protected ?int $three;

    public function __construct(?int $one = null, ?int $two = null, ?int $three = null)
    {
        $this->one = $one;
        $this->two = $two;
        $this->three = $three;
    }

    public function getOne(): ?int
    {
        return $this->one;
    }

    public function getTwo(): ?int
    {
        return $this->two;
    }

    public function getThree(): ?int
    {
        return $this->three;
    }

    public function getType(): string
    {
        return "";
    }

    public function getRelationships(): ObjectDescriptionList
    {
        return new ObjectDescriptionList();
    }

    public function getFields(): array
    {
        return [];
    }

    public function getDefaultFields(): array
    {
        return [];
    }

}

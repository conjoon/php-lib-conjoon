<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2019-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Tests\Conjoon\MailClient\Data\CompoundKey;

use Conjoon\Data\Identifier;
use Conjoon\MailClient\Data\CompoundKey\CompoundKey;
use Conjoon\Core\Contract\Stringable;
use Tests\StringableTestTrait;
use Tests\TestCase;

/**
 * Tests CompoundKey
 */
class CompoundKeyTest extends TestCase
{
    use StringableTestTrait;

    /**
     * Test class
     */
    public function testClass()
    {
        $key = $this->createMockForAbstract(CompoundKey::class, [], ["a", "b"]);
        $this->assertInstanceOf(Stringable::class, $key);
        $this->assertInstanceOf(Identifier::class, $key);
    }


    /**
     * Tests toString()
     */
    public function testToString()
    {
        $this->runToStringTest(CompoundKey::class);
    }
}

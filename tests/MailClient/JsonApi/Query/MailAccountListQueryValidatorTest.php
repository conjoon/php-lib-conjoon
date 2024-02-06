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

namespace Tests\Conjoon\MailClient\JsonApi\Query;


use Conjoon\JsonApi\Query\Validation\CollectionQueryValidator;
use Conjoon\MailClient\JsonApi\Query\MailAccountListQueryValidator;
use Tests\TestCase;

class MailAccountListQueryValidatorTest extends TestCase
{

    public function testClass()
    {
        $inst = new MailAccountListQueryValidator();
        $this->assertInstanceOf(CollectionQueryValidator::class, $inst);
    }
}

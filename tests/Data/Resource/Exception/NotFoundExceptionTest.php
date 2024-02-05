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

namespace Tests\Conjoon\MailClient\Exception;

use Conjoon\Data\Resource\Exception\NotFoundException as ResourceNotFoundException;
use RuntimeException;
use Tests\TestCase;

class NotFoundExceptionTest extends TestCase
{
    public function testInstance()
    {

        $exception = $this->getMockForAbstractClass(ResourceNotFoundException::class);

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }
}

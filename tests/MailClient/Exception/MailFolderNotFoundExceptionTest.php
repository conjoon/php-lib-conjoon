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

use Conjoon\MailClient\Exception\MailFolderNotFoundException;
use Conjoon\Data\Resource\Exception\NotFoundException as ResourceNotFoundException;
use Tests\TestCase;

class MailFolderNotFoundExceptionTest extends TestCase
{
    public function testInstance()
    {

        $exception = new MailFolderNotFoundException();

        $this->assertInstanceOf(ResourceNotFoundException::class, $exception);
    }
}

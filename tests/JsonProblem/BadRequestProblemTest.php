<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2021-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Tests\Conjoon\JsonProblem;

use Conjoon\JsonProblem\BadRequestProblem;
use Conjoon\JsonProblem\AbstractProblem;
use Conjoon\Http\StatusCodes as Status;
use Tests\TestCase;


class BadRequestProblemTest extends TestCase
{
    /**
     * test instance
     */
    public function testInstance()
    {
        $problem = new BadRequestProblem();

        $this->assertInstanceOf(AbstractProblem::class, $problem);

        $this->assertSame(400, $problem->getStatus());
        $this->assertSame(Status::HTTP_STATUS[Status::HTTP_400], $problem->getTitle());
    }
}

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

namespace Tests\Conjoon\Core\Data\Error;

use Conjoon\Core\Data\Error\AbstractError;
use Conjoon\Core\Data\Error\AnonymousErrorSource;
use Conjoon\Core\Data\Error\Error;
use Conjoon\Core\Data\Error\ErrorSource;
use stdClass;
use Tests\TestCase;

/**
 * tests AbstractError
 */
class AbstractErrorTest extends TestCase
{
    /**
     * Tests functionality
     */
    public function testClass()
    {
        $errorSource = $this->createMockForAbstract(ErrorSource::class);
        $error = $this->createMockForAbstract(AbstractError::class, [], [
            $errorSource,
            "detail",
            500
        ]);

        $this->assertInstanceOf(Error::class, $error);
        $this->assertSame($errorSource, $error->getSource());
        $this->assertSame("detail", $error->getDetails());
        $this->assertSame(500, $error->getCode());

        $std = new stdClass();
        $error = $this->createMockForAbstract(AbstractError::class, [], [
            $std
        ]);
        $this->assertSame("", $error->getDetails());
        $this->assertSame(0, $error->getCode());

        $this->assertInstanceOf(AnonymousErrorSource::class, $error->getSource());
        $this->assertSame($std, $error->getSource()->getSource());

        $this->assertEquals([
            "source" => $error->getSource()->toArray(),
            "detail" => $error->getDetails(),
            "code" => $error->getCode()
        ], $error->toArray());
    }
}

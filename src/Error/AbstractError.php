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

namespace Conjoon\Error;

/**
 * Abstract representation of an error.
 */
abstract class AbstractError implements ErrorObject
{
    /**
     * @var string
     */
    private string $details;

    /**
     * @var int
     */
    private int $code;

    /**
     * @var ErrorSource
     */
    private ErrorSource $source;

    /**
     * Constructor.
     *
     * @param Object $source The source of the error. If the object is not of type ErrorSource,
     * it will be wrapped in an instance of AnonymousErrorSource.
     * @param string $details
     * @param int $code
     */
    public function __construct(object $source, string $details = "", int $code = 0)
    {
        if (!$source instanceof ErrorSource) {
            $source = new AnonymousErrorSource($source);
        }

        $this->source = $source;
        $this->details = $details;
        $this->code = $code;
    }

    /**
     * @inheritdoc
     */
    public function getSource(): ErrorSource
    {
        return $this->source;
    }

    /**
     * @inheritdoc
     */
    public function getDetails(): string
    {
        return $this->details;
    }

    /**
     * @inheritdoc
     */
    public function getCode(): int
    {
        return $this->code;
    }


    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            "code"   => $this->getCode(),
            "source" => $this->getSource()->toArray(),
            "detail" => $this->getDetails()
        ];
    }
}

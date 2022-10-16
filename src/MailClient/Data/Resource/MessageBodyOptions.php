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

namespace Conjoon\MailClient\Data\Resource;

use Conjoon\Mime\MimeType;
use InvalidArgumentException;

/**
 * Contract for options representing MessageBodyOptions.
 *
 */
abstract class MessageBodyOptions
{
    /**
     * Returns the length the text for the MessageBody should be trimmed to.
     * Returns null if this option is not available.
     *
     * @param MimeType $mimeType any of MimeType::TEXT_HTML or MimeType::TEXT_PLAIN
     *
     * @return int|null
     *
     * @throws InvalidArgumentException if MimeType does not equal to MimeType::TEXT_HTML
     * or MimeType::TEXT_PLAIN
     */
    abstract public function getLength(MimeType $mimeType): ?int;


    /**
     * Returns true if the api should trim the text to getLength(), otherwise false.
     * Returns null if this options is not available.
     *
     * @param MimeType $mimeType any of MimeType::TEXT_HTML or MimeType::TEXT_PLAIN
     *
     * @return bool|null
     *
     * @throws InvalidArgumentException if MimeType does not equal to MimeType::TEXT_HTML
     * or MimeType::TEXT_PLAIN
     */
    abstract public function getTrimApi(MimeType $mimeType): ?bool;
}

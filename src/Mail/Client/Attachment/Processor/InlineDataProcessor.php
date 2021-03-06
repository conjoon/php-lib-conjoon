<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2019-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Conjoon\Mail\Client\Attachment\Processor;

use Conjoon\Mail\Client\Attachment\FileAttachment;
use Conjoon\Mail\Client\Attachment\FileAttachmentItem;

/**
 * Class InlineDataProcessor.
 * Provides inline data for downloadUrl and previewImgSrc, if applicable
 *
 * @package Conjoon\Mail\Client\Attachment\Processor
 */
class InlineDataProcessor implements FileAttachmentProcessor
{
// +----------------------------
// | FileAttachmentProcessor
// +----------------------------
//
    /**
     * @inheritdoc
     */
    public function process(FileAttachment $fileAttachment): FileAttachmentItem
    {

        $content = $fileAttachment->getContent();
        $encoding = $fileAttachment->getEncoding();

        if ($encoding !== "base64") {
            throw new ProcessorException("unexpected encoding-information \"" . $encoding . "\"");
        }

        $type = $fileAttachment->getType();

        $data = [
            "size" => $fileAttachment->getSize(),
            "type" => $type,
            "text" => $fileAttachment->getText(),
            "downloadUrl" => 'data:application/octet-stream;base64,' . $content,
            "previewImgSrc" => stripos($type, "image") === 0
                ? "data:" . $type . ";base64," . $content
                : ""
        ];

        return new FileAttachmentItem($fileAttachment->getAttachmentKey(), $data);
    }
}

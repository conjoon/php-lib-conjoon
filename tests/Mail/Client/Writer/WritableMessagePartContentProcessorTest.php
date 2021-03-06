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

namespace Tests\Conjoon\Mail\Client\Writer;

use Conjoon\Mail\Client\Message\Text\AbstractMessagePartContentProcessor;
use Conjoon\Mail\Client\Writer\HtmlWritableStrategy;
use Conjoon\Mail\Client\Writer\PlainWritableStrategy;
use Conjoon\Mail\Client\Writer\WritableMessagePartContentProcessor;
use Conjoon\Text\Converter;
use Tests\Conjoon\Mail\Client\Message\Text\AbstractMessagePartContentProcessorTest;

/**
 * Class WritableMessagePartContentProcessorTest
 * @package Tests\Conjoon\Mail\Client\Writer
 */
class WritableMessagePartContentProcessorTest extends AbstractMessagePartContentProcessorTest
{
    /**
     * Test instance.
     */
    public function testInstance()
    {
        $processor = $this->createProcessor();
        $this->assertInstanceOf(AbstractMessagePartContentProcessor::class, $processor);
    }


// +--------------------------
// | Helper
// +--------------------------

    /**
     * @return WritableMessagePartContentProcessor
     */
    protected function createProcessor(): WritableMessagePartContentProcessor
    {

        return new WritableMessagePartContentProcessor(
            $this->createConverter(),
            $this->createPlainWritableStrategy(),
            $this->createHtmlWritableStrategy()
        );
    }


    /**
     * @return Converter
     */
    protected function createConverter(): Converter
    {

        return new class implements Converter {
            public function convert(string $text, string $fromCharset, string $targetCharset): string
            {
                return implode(" ", [$text, $fromCharset, $targetCharset]);
            }
        };
    }

    /**
     * @return HtmlWritableStrategy
     */
    protected function createHtmlWritableStrategy(): HtmlWritableStrategy
    {

        return new class implements HtmlWritableStrategy {
            public function process(string $text): string
            {
                return "<HTML>" . $text;
            }
        };
    }

    /**
     * @return PlainWritableStrategy
     */
    protected function createPlainWritableStrategy(): PlainWritableStrategy
    {

        return new class implements PlainWritableStrategy {
            public function process(string $text): string
            {
                return "PLAIN" . $text;
            }
        };
    }
}

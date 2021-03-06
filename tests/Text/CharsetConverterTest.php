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

namespace Tests\Conjoon\Text;

use Conjoon\Text\CharsetConverter;
use Conjoon\Text\Converter;
use Tests\TestCase;

/**
 * Class CharsetConverterTest
 * @package Tests\Conjoon\Text
 */
class CharsetConverterTest extends TestCase
{
    /**
     * Test inheritance
     */
    public function testInstance()
    {

        $decoder = new CharsetConverter();
        $this->assertInstanceOf(Converter::class, $decoder);
    }


    /**
     * Test convert()
     */
    public function testConvert()
    {
        $decoder = new CharsetConverter();
        $text = "randomstring";

        $this->assertSame($text, $decoder->convert($text, "ISO-8859-1", "UTF8"));

        $text = "€";
        $currentLocale = setlocale(LC_ALL, 0);
        setLocale(LC_ALL, "en_US.UTF-8");
        $this->assertSame("EUR", $decoder->convert($text, "UTF-8", "ISO-8859-1"));
        setLocale(LC_ALL, $currentLocale);

        $text = "€";
        $this->assertSame($text, $decoder->convert($text, "UTF-8"));

        $text = "€";
        $this->assertSame($text, $decoder->convert($text));
    }
}

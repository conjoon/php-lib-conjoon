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

namespace Conjoon\Net\Uri\Component\Path;

use Conjoon\Core\Contract\Equatable;
use Conjoon\Core\Contract\Stringable;
use Conjoon\Core\Contract\StringStrategy;
use Conjoon\Core\Exception\InvalidTypeException;
use Conjoon\Net\Uri;

/**
 * Class representing a template for the Path-Part of a Uniform Resource Identifier (URI).
 * The implementation follows loosely https://datatracker.ietf.org/doc/html/rfc6570. It provides
 * template functionality for the path-part of a URI.
 *
 * @example
 *    $tpl = new PathTemplate("/MailFolder/{mailFolderId}");
 *    $tpl->getVariableNames(); // ["mailFolderId"]
 *
 *    $tpl->match(Uri::create("https://localhost:8080/MailFolder/2?query=value"));
 *    // ["mailFolderId" => "2"]
 *
 *    $tpl->match(Uri::create("https://localhost:8080/path/MailFolder/2?query=value"));
 *    // since the PathTemplate represents an absolute path, the returned value is null
 *
 *    $tpl = new PathTemplate("MailFolder/{mailFolderId}");
 *    $tpl->match(Uri::create("https://localhost:8080/pathMailFolder/2?query=value"));
 *    // ["mailFolderId" => "2"]
 *
 */
class Template implements Stringable, Equatable
{
    /**
     * @var string
     */
    private readonly string $templateString;

    /**
     * @var ?array<int, string>
     */
    private ?array $pathParameterNames = null;

    /**
     * @var TemplateRegex|null
     */
    private ?TemplateRegex $uriTemplateRegex = null;

    /**
     * Constructor.
     *
     * @param string $templateString
     */
    public function __construct(string $templateString)
    {
        $this->templateString = $templateString;
    }


    /**
     * If the submitted URI matches this template, an array of strings is returned,
     * whereas the keys are the matched variable-names with their appropriate values.
     *
     * @example Example:
     * $template = new PathTemplate("accounts/{user}/address/{shippingAddress}");
     * $template->match(new Uri("http://example.com/accounts/1/address/42"));
     * // ["user" => 1, "shippingAddress" => 42]
     *
     * @param Uri $uri
     *
     * @return array<string, string>|null
     */
    public function match(Uri $uri): ?array
    {
        $uriRegex = $this->getUriTemplateRegex();
        $matches = $uriRegex->match($uri->getPath());

        if ($matches === null) {
            return null;
        }

        $pathParameterList = [];

        foreach ($matches as $name => $value) {
            $pathParameterList[$name] = $value;
        }

        return $pathParameterList;
    }


    /**
     * Returns all variable names found in this template.
     *
     * @return array<int, string>
     */
    public function getVariableNames(): array
    {
        if ($this->pathParameterNames) {
            return $this->pathParameterNames;
        }

        $prepReg = "/{(.+?)}/m";

        $this->pathParameterNames = [];

        preg_match_all($prepReg, $this->templateString, $matches, PREG_SET_ORDER, 0);

        foreach ($matches as $match) {
            $this->pathParameterNames[] = $match[1];
        }

        return $this->pathParameterNames;
    }


    /**
     * @return TemplateRegex
     */
    private function getUriTemplateRegex(): TemplateRegex
    {
        if (!$this->uriTemplateRegex) {
            $this->uriTemplateRegex = new TemplateRegex($this->templateString);
        }
        return $this->uriTemplateRegex;
    }


    /**
     * @param StringStrategy|null $stringStrategy
     * @return string
     */
    public function toString(StringStrategy $stringStrategy = null): string
    {
        return $stringStrategy ? $stringStrategy->toString($this) : $this->templateString;
    }


    /**
     * @param object $target
     * @return bool
     */
    public function equals(object $target): bool
    {
        if (!($target instanceof Template)) {
            throw new InvalidTypeException(
                "\"target\" must be an instance of " . static::class . ", is " . get_class($target)
            );
        }

        return strtolower($this->toString()) === strtolower($target->toString());
    }
}

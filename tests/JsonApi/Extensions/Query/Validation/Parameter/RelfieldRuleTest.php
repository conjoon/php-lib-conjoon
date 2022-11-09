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

namespace Tests\Conjoon\JsonApi\Query\Validation\Parameter;

use Conjoon\Data\Validation\ValidationError;
use Conjoon\Data\Validation\ValidationErrors;
use Conjoon\Net\Uri\Component\Query\Parameter;
use Conjoon\JsonApi\Extensions\Query\Validation\Parameter\RelfieldRule;
use Conjoon\JsonApi\Query\Validation\Parameter\FieldsetRule;
use Conjoon\Data\Resource\ObjectDescription;
use Conjoon\Data\Resource\ObjectDescriptionList;
use ReflectionException;
use Tests\TestCase;

/**
 * Tests IncludeParameterRule
 */
class RelfieldRuleTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $list = $this->getResourceObjectDescriptionList();
        $includes = $this->getIncludes();
        $rule = new RelfieldRule($list, $includes);
        $this->assertInstanceOf(FieldsetRule::class, $rule);

        $wildcardEnabled = $this->makeAccessible($rule, "wildcardEnabled", true);

        $this->assertFalse($wildcardEnabled->getValue($rule));
    }


    /**
     * tests supports()
     */
    public function testSupports()
    {
        $includes = $this->getIncludes();
        $rule = new RelfieldRule($this->getResourceObjectDescriptionList(), $includes);

        $this->assertTrue($rule->supports(new Parameter("relfield:fields[MessageItem]", "")));
        $this->assertFalse($rule->supports(new Parameter("field[MessageItem]", "")));
        $this->assertFalse($rule->supports(new Parameter("fields", "")));
    }


    /**
     * tests validate() with empty fieldset "relfield:fields[MessageItem]="
     */
    public function testValidateWithEmptyRelfield()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("relfield:fields[MessageItem]", "");
        $this->assertTrue($validate->invokeArgs($rule, [$parameter, $errors]));
    }


    /**
     * tests validate() with wildcard only "relfield:fields[MessageItem]=*"
     */
    public function testValidateWithWildcardOnly()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("relfield:fields[MessageItem]", "*");
        $this->assertTrue($validate->invokeArgs($rule, [$parameter, $errors]));
    }


    /**
     * tests validate() with enableWildcard=false, wildcard only "relfield:fields[MessageItem]=*"
     */
    public function testValidateWithWildcardNotEnabled()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup(false);

        $parameter = new Parameter("relfield:fields[MessageItem]", "*");
        $this->assertFalse($validate->invokeArgs($rule, [$parameter, $errors]));
        $this->assertStringContainsString(
            "does not support wildcards",
            $errors[0]->getDetails()
        );
    }


    /**
     * tests validate() with wildcard and valid fields "relfield:fields[MessageItem]=*,+previewText,+date"
     */
    public function testValidateWithWildcardAndValidFields()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("relfield:fields[MessageItem]", "*,+previewText,+date");
        $this->assertTrue($validate->invokeArgs($rule, [$parameter, $errors]));
    }


    /**
     * tests validate() with wildcard and invalid fields "relfield:fields[MessageItem]=+MailFolder,*,-date"
     */
    public function testValidateWithWildcardAndInvalidFields()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("relfield:fields[MessageItem]", "*,+MailFolder,-date");
        $this->assertFalse($validate->invokeArgs($rule, [$parameter, $errors]));

        $this->assertInstanceOf(ValidationError::class, $errors[0]);
        $this->assertStringContainsString(
            "The following fields for \"relfield:fields[MessageItem]\" cannot be found",
            $errors[0]->getDetails()
        );
        $this->assertSame(400, $errors[0]->getCode());
        $this->assertSame($parameter, $errors[0]->getSource());
    }


    /**
     * tests validate() with invalid fields "relfield:fields[MessageItem]=+MailFolder,-date"
     */
    public function testValidateWithInvalidFields()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("relfield:fields[MessageItem]", "+MailFolder,-date");
        $this->assertFalse($validate->invokeArgs($rule, [$parameter, $errors]));

        $this->assertInstanceOf(ValidationError::class, $errors[0]);
        $this->assertStringContainsString(
            "The following fields for \"relfield:fields[MessageItem]\" cannot be found",
            $errors[0]->getDetails()
        );
        $this->assertSame(400, $errors[0]->getCode());
        $this->assertSame($parameter, $errors[0]->getSource());
    }


    /**
     * tests validate() with valid fields "relfield:fields[MessageItem]=+subject,+date"
     */
    public function testValidateWithValidFields()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("relfield:fields[MessageItem]", "+subject,+date");
        $this->assertTrue($validate->invokeArgs($rule, [$parameter, $errors]));
    }


    /**
     * tests validate() with missing prefix "relfield:fields[MessageItem]=+subject,date"
     */
    public function testValidateWithValidFieldsButMissingPrefix()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("relfield:fields[MessageItem]", "+subject,date");
        $this->assertFalse($validate->invokeArgs($rule, [$parameter, $errors]));

        $this->assertStringContainsString(
            "expects each field to be prefixed",
            $errors[0]->getDetails()
        );
    }


    /**
     * tests validate() with missing prefixes for all fields, which
     * should fall back to default sparse fieldset behavior "relfield:fields[MessageItem]=subject,date"
     */
    public function testValidateWithValidFieldsAndAllMissingPrefixes()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("relfield:fields[MessageItem]", "subject,date");
        $this->assertFalse(
            $validate->invokeArgs($rule, [$parameter, $errors])
        );
    }


    /**
     * tests validate() with missing prefixes for all fields, but with wildcard
     * "relfield:fields[MessageItem]=*,subject,date"
     */
    public function testValidateWithValidFieldsAndAllMissingPrefixesAndWildcard()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("relfield:fields[MessageItem]", "*,subject,date");
        $this->assertFalse(
            $validate->invokeArgs($rule, [$parameter, $errors])
        );
        $this->assertStringContainsString(
            "each field to be prefixed",
            $errors[0]->getDetails()
        );
    }


    /**
     * tests validate() with multiple wildcards "relfield:fields[MessageItem]=*,+subject,*,-date"
     */
    public function testValidateWithMultipleWildcards()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("relfield:fields[MessageItem]", "*,+subject,*,-date");
        $this->assertFalse($validate->invokeArgs($rule, [$parameter, $errors]));

        $this->assertStringContainsString(
            "does not allow more than one wildcard for \"relfield:fields[MessageItem]\"",
            $errors[0]->getDetails()
        );
    }


    /**
     * @return ObjectDescriptionList
     */
    protected function getResourceObjectDescriptionList(): ObjectDescriptionList
    {
        $list = new ObjectDescriptionList();

        $messageItem = $this->createMockForAbstract(ObjectDescription::class, ["getType", "getFields"]);
        $messageItem->expects($this->any())->method("getType")->willReturn("MessageItem");
        $messageItem->expects($this->any())->method("getFields")->willReturn([
            "subject", "date", "from", "to", "previewText"
        ]);

        $mailFolder = $this->createMockForAbstract(ObjectDescription::class, ["getType", "getFields"]);
        $mailFolder->expects($this->any())->method("getType")->willReturn("MailFolder");
        $mailFolder->expects($this->any())->method("getFields")->willReturn([
            "name", "type", "id"
        ]);

        $list[] = $messageItem;
        $list[] = $mailFolder;

        return $list;
    }


    /**
     * @return string[]
     */
    protected function getIncludes()
    {
        return ["MailFolder", "MessageItem"];
    }


    /**
     * @return array
     * @throws ReflectionException
     */
    protected function getValidateTestSetup(bool $enableWildcard = true): array
    {
        $errors = new ValidationErrors();
        $includes = $this->getIncludes();
        $list = $this->getResourceObjectDescriptionList();
        $rule = new RelfieldRule($list, $includes, $enableWildcard);
        $validate = $this->makeAccessible($rule, "validate");

        return [
            "includes" => $includes,
            "errors" => $errors,
            "list" => $list,
            "rule" => $rule,
            "validate" => $validate
        ];
    }
}

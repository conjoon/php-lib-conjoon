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
use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\ParameterTrait;
use Conjoon\Http\Query\Validation\Parameter\ParameterRule;
use Conjoon\Data\Resource\ObjectDescription;
use Conjoon\Data\Resource\ObjectDescriptionList;
use Conjoon\JsonApi\Query\Validation\Parameter\FieldsetRule;
use ReflectionException;
use Tests\TestCase;

/**
 * Tests IncludeParameterRule
 */
class FieldsetRuleTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $list = $this->getResourceObjectDescriptionList();
        $includes = $this->getIncludes();
        $rule = new FieldsetRule($list, $includes);
        $this->assertInstanceOf(ParameterRule::class, $rule);

        $uses = class_uses(FieldsetRule::class);
        $this->assertContains(ParameterTrait::class, $uses);

        $this->assertSame($list, $rule->getResourceObjectDescriptions());
        $this->assertSame($includes, $rule->getIncludes());
    }


    /**
     * tests supports()
     */
    public function testSupports()
    {
        $includes = $this->getIncludes();
        $rule = new FieldsetRule($this->getResourceObjectDescriptionList(), $includes);

        $this->assertTrue($rule->supports(new Parameter("fields[MessageItem]", "")));
        $this->assertFalse($rule->supports(new Parameter("field[MessageItem]", "")));
        $this->assertFalse($rule->supports(new Parameter("fields", "")));
    }


    /**
     * tests getFields()
     */
    public function testGetFields()
    {
        $includes = $this->getIncludes();
        $list = $this->getResourceObjectDescriptionList();
        $rule = new FieldsetRule($list, $includes);
        $getFields = $this->makeAccessible($rule, "getFields");

        $this->assertEquals(
            ["subject", "date", "from", "to", "previewText"],
            $getFields->invokeArgs($rule, ["MessageItem"])
        );

        $this->assertNull(
            $getFields->invokeArgs($rule, ["NotAvailabe"])
        );
    }

    /**
     * tests validate() with missing include "fields[unknown]=field1,field2"
     */
    public function testValidateWithMissingInclude()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("fields[unknown]", "field1,field2");
        $this->assertFalse($validate->invokeArgs($rule, [$parameter, $errors]));
        $this->assertInstanceOf(ValidationError::class, $errors[0]);
        $this->assertStringContainsString(
            "The requested type \"unknown\" cannot be found in the list of includes",
            $errors[0]->getDetails()
        );
        $this->assertSame(400, $errors[0]->getCode());
        $this->assertSame($parameter, $errors[0]->getSource());
    }
    /**
     * tests validate() with key for fieldset that is not available in relationships "fields[unknown]=field1,field2"
     */
    public function testValidateWithUnknownType()
    {
        $errors = new ValidationErrors();
        $includes = array_merge(["unknown"], $this->getIncludes());
        $list = $this->getResourceObjectDescriptionList();
        $rule = new FieldsetRule($list, $includes);
        $validate = $this->makeAccessible($rule, "validate");

        $parameter = new Parameter("fields[unknown]", "field1,field2");
        $this->assertFalse($validate->invokeArgs($rule, [$parameter, $errors]));
        $this->assertInstanceOf(ValidationError::class, $errors[0]);
        $this->assertStringContainsString(
            "Cannot find fields for parameter \"fields[unknown]\"",
            $errors[0]->getDetails()
        );
        $this->assertSame(400, $errors[0]->getCode());
        $this->assertSame($parameter, $errors[0]->getSource());
    }


    /**
     * tests validate() with empty fieldset "fields[MessageItem]="
     */
    public function testValidateWithEmptyFieldset()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("fields[MessageItem]", "");
        $this->assertTrue($validate->invokeArgs($rule, [$parameter, $errors]));
    }

    /**
     * tests validate() with wildcard only "fields[MessageItem]=*"
     * @note Wildcard moved to relfield-extension
     */
    public function testValidateWithWildcardOnly()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("fields[MessageItem]", "*");
        $this->assertFalse($validate->invokeArgs($rule, [$parameter, $errors]));
    }


    /**
     * tests validate() with wildcard and valid fields "fields[MessageItem]=*,previewText,date"
     * @note Wildcard moved to relfield-extension
     */
    public function testValidateWithWildcardAndValidFields()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("fields[MessageItem]", "*,previewText,date");
        $this->assertFalse($validate->invokeArgs($rule, [$parameter, $errors]));
    }


    /**
     * tests validate() with wildcard and invalid fields "fields[MessageItem]=MailFolder,date"
     */
    public function testValidateWithWildcardAndInvalidFields()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("fields[MessageItem]", "MailFolder,date");
        $this->assertFalse($validate->invokeArgs($rule, [$parameter, $errors]));

        $this->assertInstanceOf(ValidationError::class, $errors[0]);
        $this->assertStringContainsString(
            "The following fields for \"fields[MessageItem]\" cannot be found",
            $errors[0]->getDetails()
        );
        $this->assertSame(400, $errors[0]->getCode());
        $this->assertSame($parameter, $errors[0]->getSource());
    }


    /**
     * tests validate() with invalid fields "fields[MessageItem]=MailFolder,date"
     */
    public function testValidateWithInvalidFields()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("fields[MessageItem]", "MailFolder,date");
        $this->assertFalse($validate->invokeArgs($rule, [$parameter, $errors]));

        $this->assertInstanceOf(ValidationError::class, $errors[0]);
        $this->assertStringContainsString(
            "The following fields for \"fields[MessageItem]\" cannot be found",
            $errors[0]->getDetails()
        );
        $this->assertSame(400, $errors[0]->getCode());
        $this->assertSame($parameter, $errors[0]->getSource());
    }


    /**
     * tests validate() with invalid fields "fields[MessageItem]=subject,date"
     */
    public function testValidateWithValidFields()
    {
        list("errors" => $errors, "rule" => $rule, "validate" => $validate) = $this->getValidateTestSetup();

        $parameter = new Parameter("fields[MessageItem]", "subject,date");
        $this->assertTrue($validate->invokeArgs($rule, [$parameter, $errors]));
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
    protected function getValidateTestSetup(): array
    {
        $errors = new ValidationErrors();
        $includes = $this->getIncludes();
        $list = $this->getResourceObjectDescriptionList();
        $rule = new FieldsetRule($list, $includes);
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

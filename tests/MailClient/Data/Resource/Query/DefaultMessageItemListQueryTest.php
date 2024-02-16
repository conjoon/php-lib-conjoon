<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Tests\Conjoon\MailClient\Data\Resource\Query;

use Conjoon\Data\ParameterBag;
use Conjoon\Data\Resource\ResourceDescription;
use Conjoon\MailClient\Data\Resource\MessageBodyDescription;
use Conjoon\MailClient\Data\Resource\MessageItemDescription;
use Conjoon\MailClient\Data\Resource\Query\DefaultMessageItemListQuery;
use Conjoon\MailClient\Data\Resource\Query\MessageItemListQuery;
use Conjoon\Math\Expression\Notation\PolishNotation;
use Conjoon\Mime\MimeType;
use Tests\TestCase;


class DefaultMessageItemListQueryTest extends TestCase
{

    public function testClass()
    {
        $inst = $this->createMessageItemListQuery();
        $this->assertInstanceOf(MessageItemListQuery::class,$inst);
    }

    public function testGetField() {

        $bag = new ParameterBag(["fields[MessageItem]" => "subject,from"]);
        $resourceQuery = $this->createMessageItemListQuery($bag);
        $fields = $resourceQuery->getFields(MessageItemDescription::class);

        $this->assertEqualsCanonicalizing(["subject", "from"], $fields);

        $bag = new ParameterBag([
            "fields[MessageItem]" => "",
            "include" => "MessageBody",
            "relfield:fields[MessageBody]" => "-textHtml"
        ]);
        $resourceQuery = $this->createMessageItemListQuery($bag);

        $this->assertEqualsCanonicalizing(
            ["textPlain"],
            $resourceQuery->getFields(MessageBodyDescription::class)
        );

        $this->assertEqualsCanonicalizing(
            [],
            $resourceQuery->getFields(MessageItemDescription::class)
        );
    }


    public function testGetOptions() {

        // no options available
        $bag = new ParameterBag(["options" => "{}"]);
        $resourceQuery = $this->createMessageItemListQuery($bag);
        $this->assertNull($resourceQuery->getOptions());

        $jsonFilter = [];
        $bag = new ParameterBag(["include" => "MessageBody", "options[MessageBody]" => json_encode($jsonFilter)]);
        $resourceQuery = $this->createMessageItemListQuery($bag);
        $options = $resourceQuery->getOptions(MessageBodyDescription::class);
        $this->assertNotNull($options);
        $this->assertNull($options->getLength(MimeType::TEXT_HTML));

        $jsonFilter = ["textPlain" => ["length" => "200", "trimApi" => true]];
        $bag = new ParameterBag(["include" => "MessageBody", "options[MessageBody]" => json_encode($jsonFilter)]);
        $resourceQuery = $this->createMessageItemListQuery($bag);
        $options = $resourceQuery->getOptions(MessageBodyDescription::class);
        $this->assertSame(200, $options->getLength(MimeType::TEXT_PLAIN));
        $this->assertSame(true, $options->getTrimApi(MimeType::TEXT_PLAIN));
    }


    private function createMessageItemListQuery(?ParameterBag $parameterBag = null): DefaultMessageItemListQuery {
        if (!$parameterBag) {
            $parameterBag = new ParameterBag();
        }
        return new DefaultMessageItemListQuery($parameterBag);
    }
}

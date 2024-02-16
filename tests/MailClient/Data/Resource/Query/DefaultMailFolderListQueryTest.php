<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2022-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Tests\Conjoon\MailClient\Data\Resource\Query;

use Conjoon\Data\ParameterBag;
use Conjoon\MailClient\Data\Resource\Query\DefaultMailFolderListQuery;
use Conjoon\MailClient\Data\Resource\Query\MailFolderListQuery;
use Conjoon\Math\Expression\Notation\PolishNotation;
use Tests\TestCase;


class DefaultMailFolderListQueryTest extends TestCase
{

    public function testClass()
    {
        $inst = $this->createMailFolderListQuery();
        $this->assertInstanceOf(MailFolderListQuery::class,$inst);
    }

    public function testGetField() {
        $bag = new ParameterBag(["fields[MailFolder]" => "unreadMessages,totalMessages"]);
        $resourceQuery = $this->createMailFolderListQuery($bag);
        $fields = $resourceQuery->getFields();
        $this->assertEqualsCanonicalizing(["unreadMessages", "totalMessages"], $fields);

        $bag = new ParameterBag(["relfield:fields[MailFolder]" => "-unreadMessages,-totalMessages"]);
        $resourceQuery = $this->createMailFolderListQuery($bag);
        $fields = $resourceQuery->getFields();
        $this->assertEqualsCanonicalizing(["name", "folderType", "data"], $fields);
    }

    public function testGetFilter() {
        $jsonFilter = ["in" => ["id" => ["[GMAIL]", "INBOX"]]];
        $bag = new ParameterBag(["filter" => json_encode($jsonFilter)]);

        $resourceQuery = $this->createMailFolderListQuery($bag);

        $this->assertSame(
            "IN id ([GMAIL], INBOX)",
            $resourceQuery->getFilter()->getExpression()->toString(new PolishNotation())
        );
    }

    public function testGetOptions() {

        // no options available
        $bag = new ParameterBag(["options" => "{}"]);
        $resourceQuery = $this->createMailFolderListQuery($bag);
        $this->assertNull($resourceQuery->getOptions());

        // options available, no dissolveNamespaces set
        $jsonFilter = [];
        $bag = new ParameterBag(["options[MailFolder]" => json_encode($jsonFilter)]);
        $resourceQuery = $this->createMailFolderListQuery($bag);
        $this->assertNotNull($resourceQuery->getOptions());
        $this->assertEqualsCanonicalizing([], $resourceQuery->getOptions()->getDissolveNamespaces());

        // options available, dissolveNamespaces is set
        $jsonFilter = ["dissolveNamespaces" => ["INBOX", "[GMAIL]"]];
        $bag = new ParameterBag(["options[MailFolder]" => json_encode($jsonFilter)]);
        $resourceQuery = $this->createMailFolderListQuery($bag);
        $this->assertNotNull($resourceQuery->getOptions());
        $this->assertEqualsCanonicalizing(["INBOX", "[GMAIL]"], $resourceQuery->getOptions()->getDissolveNamespaces());
    }


    private function createMailFolderListQuery(?ParameterBag $parameterBag = null): DefaultMailFolderListQuery {
        if (!$parameterBag) {
            $parameterBag = new ParameterBag();
        }
        return new DefaultMailFolderListQuery($parameterBag);
    }
}

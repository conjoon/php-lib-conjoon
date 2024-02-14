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

namespace Conjoon\MailClient\Data\Resource;


abstract class MailFolderListOptions
{
    /**
     * @return array One or more imap namespaces that should be ignored while assembling folder structure.
     *  This allows for showing contents of an INBOX or [GMAIL]-namespace without listing this namespace
     * as a separate folder.
     */
    abstract public function getDissolveNamespaces(): array;

}

<?php

/**
 * This file is part of the conjoon/php-lib-conjoon project.
 *
 * (c) 2019-2024 Thorsten Suckow-Homberg <thorsten@suckow-homberg.de>
 *
 * For full copyright and license information, please consult the LICENSE-file distributed
 * with this source code.
 */

declare(strict_types=1);

namespace Conjoon\MailClient\Data\Protocol\Imap;

use Conjoon\Http\Exception\InternalServerErrorException;

/**
 * Class MailClientException
 *
 * @package Conjoon\Mail\Client
 */
class ImapClientException extends InternalServerErrorException
{
}

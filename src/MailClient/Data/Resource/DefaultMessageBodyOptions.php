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

use Conjoon\Mime\MimeType;
use InvalidArgumentException;

/**
 * Contract for options representing MessageBodyOptions.
 *
 */
class DefaultMessageBodyOptions extends MessageBodyOptions
{

    public function __construct(private readonly array $config) {

    }

    /**
     * @Override
     */
    public function getLength(MimeType $mimeType): ?int
    {
        if (!in_array($mimeType, [MimeType::TEXT_PLAIN, MimeType::TEXT_HTML])) {
            throw new InvalidArgumentException();
        }

        if (!array_key_exists($mimeType->value, $this->config)) {
            return null;
        }
        return (int)$this->config[$mimeType->value]["length"] ?? null;
    }


    /**
     * @Override
     */
    public function getTrimApi(MimeType $mimeType): ?bool {
        if (!in_array($mimeType, [MimeType::TEXT_PLAIN, MimeType::TEXT_HTML])) {
            throw new InvalidArgumentException();
        }

        return $this->config[$mimeType->value]["trimApi"] ?? null;
    }
}

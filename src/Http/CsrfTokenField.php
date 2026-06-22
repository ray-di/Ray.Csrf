<?php

declare(strict_types=1);

namespace Ray\Csrf\Http;

use InvalidArgumentException;

final readonly class CsrfTokenField
{
    /** Default wire name shared with {@see \Ray\Csrf\CsrfModule}. */
    public const DEFAULT_NAME = '_csrf_token';

    public function __construct(
        public string $name = self::DEFAULT_NAME,
    ) {
        if ($this->name === '') {
            throw new InvalidArgumentException('CSRF token field name must not be empty.');
        }
    }
}

<?php

declare(strict_types=1);

namespace Ray\Csrf\Http;

use InvalidArgumentException;

final readonly class CsrfTokenField
{
    public function __construct(
        public string $name = '_csrf_token',
    ) {
        if ($this->name === '') {
            throw new InvalidArgumentException('CSRF token field name must not be empty.');
        }
    }
}

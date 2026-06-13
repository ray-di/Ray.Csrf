<?php

declare(strict_types=1);

namespace Ray\Csrf\Exception;

use Throwable;

final class MissingCsrfTokenException extends ForbiddenException
{
    public function __construct(Throwable|null $previous = null)
    {
        parent::__construct('CSRF token missing.', $previous);
    }
}

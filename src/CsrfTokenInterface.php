<?php

declare(strict_types=1);

namespace Ray\Csrf;

interface CsrfTokenInterface
{
    /** @return non-empty-string */
    public function issue(): string;

    public function verify(string $candidate): bool;

    public function clear(): void;
}

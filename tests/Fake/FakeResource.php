<?php

declare(strict_types=1);

namespace Ray\Csrf\Fake;

use BEAR\Resource\ResourceObject;
use Ray\Csrf\Attribute\CsrfToken;
use Ray\Csrf\Attribute\SameOrigin;

final class FakeResource extends ResourceObject
{
    public function onGet(): static
    {
        return $this;
    }

    #[CsrfToken]
    public function onPost(): static
    {
        return $this;
    }

    #[CsrfToken('custom_token')]
    public function onPut(): static
    {
        return $this;
    }

    #[SameOrigin]
    public function onDelete(): static
    {
        return $this;
    }

    #[SameOrigin]
    #[CsrfToken]
    public function onPatch(): static
    {
        return $this;
    }
}

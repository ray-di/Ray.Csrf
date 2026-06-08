<?php

declare(strict_types=1);

namespace Ray\Csrf\Fake;

use BEAR\Resource\ResourceObject;
use Ray\Csrf\Attribute\CsrfToken;
use Ray\Csrf\Attribute\SameOrigin;

final class WiredResource extends ResourceObject
{
    #[CsrfToken]
    public function onPost(): static
    {
        $this->body = ['result' => 'ok'];

        return $this;
    }

    #[SameOrigin]
    public function onDelete(): static
    {
        $this->body = ['result' => 'ok'];

        return $this;
    }
}

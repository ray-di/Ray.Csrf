<?php

declare(strict_types=1);

namespace Ray\Csrf;

use BEAR\Resource\ResourceObject;
use Override;
use Ray\Csrf\Attribute\CsrfToken;
use Ray\Csrf\Attribute\SameOrigin;
use Ray\Csrf\Http\AllowedOrigin;
use Ray\Csrf\Http\CompositeRequestToken;
use Ray\Csrf\Http\CsrfTokenField;
use Ray\Csrf\Http\HeaderRequestToken;
use Ray\Csrf\Http\PostRequestToken;
use Ray\Csrf\Http\RequestOriginInterface;
use Ray\Csrf\Http\RequestTokenInterface;
use Ray\Csrf\Http\ResourceQueryRequestToken;
use Ray\Csrf\Http\ServerRequestOrigin;
use Ray\Csrf\Interceptor\CsrfTokenInterceptor;
use Ray\Csrf\Interceptor\SameOriginInterceptor;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

final class CsrfModule extends AbstractModule
{
    public function __construct(
        private readonly string|null $allowedOrigin = null,
        private readonly string $tokenField = '_csrf_token',
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->bind(AllowedOrigin::class)->toInstance(new AllowedOrigin($this->allowedOrigin));
        $this->bind(CsrfTokenField::class)->toInstance(new CsrfTokenField($this->tokenField));

        $this->bind(RequestOriginInterface::class)->to(ServerRequestOrigin::class);
        $this->bindInterceptor(
            $this->matcher->subclassesOf(ResourceObject::class),
            $this->matcher->annotatedWith(SameOrigin::class),
            [SameOriginInterceptor::class],
        );

        $this->bind(CsrfTokenInterface::class)->to(SessionCsrfToken::class)->in(Scope::SINGLETON);
        $this->bind(HeaderRequestToken::class);
        $this->bind(ResourceQueryRequestToken::class);
        $this->bind(PostRequestToken::class);
        $this->bind(RequestTokenInterface::class)->to(CompositeRequestToken::class);
        $this->bindInterceptor(
            $this->matcher->subclassesOf(ResourceObject::class),
            $this->matcher->annotatedWith(CsrfToken::class),
            [CsrfTokenInterceptor::class],
        );
    }
}

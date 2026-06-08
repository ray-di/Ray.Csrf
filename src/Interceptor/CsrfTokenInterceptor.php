<?php

declare(strict_types=1);

namespace Ray\Csrf\Interceptor;

use Override;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Csrf\Attribute\CsrfToken;
use Ray\Csrf\CsrfTokenInterface;
use Ray\Csrf\Exception\InvalidCsrfTokenException;
use Ray\Csrf\Exception\MissingCsrfTokenException;
use Ray\Csrf\Http\CsrfTokenField;
use Ray\Csrf\Http\RequestTokenInterface;

final readonly class CsrfTokenInterceptor implements MethodInterceptor
{
    public function __construct(
        private CsrfTokenInterface $csrf,
        private RequestTokenInterface $requestToken,
        private CsrfTokenField $defaultField,
    ) {
    }

    /** @param MethodInvocation<object> $invocation */
    #[Override]
    public function invoke(MethodInvocation $invocation): mixed
    {
        $field = $this->field($invocation);
        $submitted = $this->requestToken->submitted($invocation, $field);
        if ($submitted === null) {
            throw new MissingCsrfTokenException();
        }

        if (! $this->csrf->verify($submitted)) {
            throw new InvalidCsrfTokenException();
        }

        return $invocation->proceed();
    }

    /** @param MethodInvocation<object> $invocation */
    private function field(MethodInvocation $invocation): CsrfTokenField
    {
        $attributes = $invocation->getMethod()->getAttributes(CsrfToken::class);
        $attribute = $attributes[0]->newInstance();

        return $attribute->field === null
            ? $this->defaultField
            : new CsrfTokenField($attribute->field);
    }
}

<?php

declare(strict_types=1);

namespace Ray\Csrf\Interceptor;

use Override;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Csrf\Attribute\CsrfToken;
use Ray\Csrf\CsrfTokenInterface;
use Ray\Csrf\Exception\InvalidCsrfTokenForbiddenException;
use Ray\Csrf\Exception\LogicException;
use Ray\Csrf\Exception\MissingCsrfTokenForbiddenException;
use Ray\Csrf\Http\CsrfTokenField;
use Ray\Csrf\Http\RequestTokenInterface;

use function sprintf;

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
            throw new MissingCsrfTokenForbiddenException();
        }

        if (! $this->csrf->verify($submitted)) {
            throw new InvalidCsrfTokenForbiddenException();
        }

        return $invocation->proceed();
    }

    /** @param MethodInvocation<object> $invocation */
    private function field(MethodInvocation $invocation): CsrfTokenField
    {
        $method = $invocation->getMethod();
        $attributes = $method->getAttributes(CsrfToken::class);
        if ($attributes === []) {
            throw new LogicException(sprintf(
                'CsrfTokenInterceptor requires #[CsrfToken] on %s::%s().',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
            ));
        }

        $attribute = $attributes[0]->newInstance();

        return $attribute->field === null
            ? $this->defaultField
            : new CsrfTokenField($attribute->field);
    }
}

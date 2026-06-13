<?php

declare(strict_types=1);

namespace Ray\Csrf;

use PHPUnit\Framework\TestCase;
use Ray\Csrf\Exception\InvalidCsrfTokenException;
use Ray\Csrf\Exception\LogicException;
use Ray\Csrf\Exception\MissingCsrfTokenException;
use Ray\Csrf\Fake\FakeCsrfToken;
use Ray\Csrf\Fake\FakeInvocation;
use Ray\Csrf\Fake\FakeResource;
use Ray\Csrf\Fake\FakeUri;
use Ray\Csrf\Http\CompositeRequestToken;
use Ray\Csrf\Http\CsrfTokenField;
use Ray\Csrf\Http\HeaderRequestToken;
use Ray\Csrf\Http\PostRequestToken;
use Ray\Csrf\Http\ResourceQueryRequestToken;
use Ray\Csrf\Interceptor\CsrfTokenInterceptor;

final class CsrfTokenInterceptorTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['HTTP_X_CSRF_TOKEN'] = null;
        $_POST = [];
    }

    public function testValidTokenProceeds(): void
    {
        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'valid-token';
        $invocation = new FakeInvocation(new FakeResource(), 'onPost');

        $actual = $this->interceptor()->invoke($invocation);

        $this->assertSame('proceeded', $actual);
        $this->assertTrue($invocation->proceeded);
    }

    public function testMissingTokenForbidden(): void
    {
        $this->expectException(MissingCsrfTokenException::class);

        $this->interceptor()->invoke(new FakeInvocation(new FakeResource(), 'onPost'));
    }

    public function testInvalidTokenForbidden(): void
    {
        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'invalid-token';
        $this->expectException(InvalidCsrfTokenException::class);

        $this->interceptor()->invoke(new FakeInvocation(new FakeResource(), 'onPost'));
    }

    public function testCustomField(): void
    {
        $resource = new FakeResource();
        $resource->uri = new FakeUri();
        $resource->uri->query = ['custom_token' => 'valid-token'];
        $invocation = new FakeInvocation($resource, 'onPut');

        $actual = $this->interceptor()->invoke($invocation);

        $this->assertSame('proceeded', $actual);
    }

    public function testMissingCsrfTokenAttributeIsConfigurationError(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('requires #[CsrfToken]');

        $this->interceptor()->invoke(new FakeInvocation(new FakeResource(), 'onGet'));
    }

    private function interceptor(): CsrfTokenInterceptor
    {
        return new CsrfTokenInterceptor(
            new FakeCsrfToken('valid-token'),
            new CompositeRequestToken(new HeaderRequestToken(), new ResourceQueryRequestToken(), new PostRequestToken()),
            new CsrfTokenField(),
        );
    }
}

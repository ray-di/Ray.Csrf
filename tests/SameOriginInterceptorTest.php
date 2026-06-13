<?php

declare(strict_types=1);

namespace Ray\Csrf;

use BEAR\Resource\Exception\BadRequestException;
use PHPUnit\Framework\TestCase;
use Ray\Csrf\Exception\ForbiddenException;
use Ray\Csrf\Exception\LogicException;
use Ray\Csrf\Fake\FakeInvocation;
use Ray\Csrf\Fake\FakeRequestOrigin;
use Ray\Csrf\Fake\FakeResource;
use Ray\Csrf\Http\AllowedOrigin;
use Ray\Csrf\Interceptor\SameOriginInterceptor;

final class SameOriginInterceptorTest extends TestCase
{
    public function testSameOriginFetchSiteProceeds(): void
    {
        $invocation = new FakeInvocation(new FakeResource(), 'onDelete');

        $actual = (new SameOriginInterceptor(
            new FakeRequestOrigin(fetchSite: 'same-origin'),
            new AllowedOrigin('https://example.com'),
        ))->invoke($invocation);

        $this->assertSame('proceeded', $actual);
    }

    public function testAllowedOriginNullShortCircuits(): void
    {
        $invocation = new FakeInvocation(new FakeResource(), 'onDelete');

        $actual = (new SameOriginInterceptor(
            new FakeRequestOrigin(fetchSite: 'cross-site'),
            new AllowedOrigin(null),
        ))->invoke($invocation);

        $this->assertSame('proceeded', $actual);
    }

    public function testCrossSiteForbidden(): void
    {
        $this->expectException(ForbiddenException::class);

        (new SameOriginInterceptor(
            new FakeRequestOrigin(fetchSite: 'cross-site'),
            new AllowedOrigin('https://example.com'),
        ))->invoke(new FakeInvocation(new FakeResource(), 'onDelete'));
    }

    public function testMalformedAllowedOriginIsConfigurationError(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('configured allowed origin is malformed');

        (new SameOriginInterceptor(
            new FakeRequestOrigin(fetchSite: 'same-origin'),
            new AllowedOrigin('https://example.com/path'),
        ))->invoke(new FakeInvocation(new FakeResource(), 'onDelete'));
    }

    public function testMalformedOriginBadRequest(): void
    {
        $this->expectException(BadRequestException::class);

        (new SameOriginInterceptor(
            new FakeRequestOrigin(origin: 'https://evil.example/path'),
            new AllowedOrigin('https://example.com'),
        ))->invoke(new FakeInvocation(new FakeResource(), 'onDelete'));
    }

    public function testHeaderAbsentForbidden(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('no Sec-Fetch-Site / Origin / Referer');

        (new SameOriginInterceptor(
            new FakeRequestOrigin(),
            new AllowedOrigin('https://example.com'),
        ))->invoke(new FakeInvocation(new FakeResource(), 'onDelete'));
    }

    public function testRefererSameOriginProceeds(): void
    {
        $actual = (new SameOriginInterceptor(
            new FakeRequestOrigin(referer: 'https://example.com/admin/edit?id=1'),
            new AllowedOrigin('https://example.com'),
        ))->invoke(new FakeInvocation(new FakeResource(), 'onDelete'));

        $this->assertSame('proceeded', $actual);
    }
}

<?php

declare(strict_types=1);

namespace Ray\Csrf;

use PHPUnit\Framework\TestCase;
use Ray\Csrf\Fake\TestCsrfModule;
use Ray\Csrf\Fake\WiredResource;
use Ray\Di\Injector;

final class CsrfModuleWiringTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['HTTP_X_CSRF_TOKEN'] = null;
        $_SERVER['HTTP_SEC_FETCH_SITE'] = null;
        $_POST = [];
    }

    public function testCsrfTokenInterceptorIsWired(): void
    {
        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'valid-token';
        $resource = (new Injector(new TestCsrfModule()))->getInstance(WiredResource::class);
        $actual = $resource->onPost();
        $body = $actual->body;

        $this->assertIsArray($body);
        $this->assertSame('ok', $body['result']);
    }

    public function testSameOriginInterceptorIsWired(): void
    {
        $_SERVER['HTTP_SEC_FETCH_SITE'] = 'same-origin';
        $resource = (new Injector(new TestCsrfModule()))->getInstance(WiredResource::class);
        $actual = $resource->onDelete();
        $body = $actual->body;

        $this->assertIsArray($body);
        $this->assertSame('ok', $body['result']);
    }
}

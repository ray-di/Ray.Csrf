<?php

declare(strict_types=1);

namespace Ray\Csrf;

use PHPUnit\Framework\TestCase;
use Ray\Csrf\Fake\FakeInvocation;
use Ray\Csrf\Fake\FakeResource;
use Ray\Csrf\Fake\FakeUri;
use Ray\Csrf\Http\CompositeRequestToken;
use Ray\Csrf\Http\CsrfTokenField;
use Ray\Csrf\Http\HeaderRequestToken;
use Ray\Csrf\Http\PostRequestToken;
use Ray\Csrf\Http\ResourceQueryRequestToken;

final class RequestTokenTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['HTTP_X_CSRF_TOKEN'] = null;
        $_POST = [];
    }

    public function testHeaderRequestToken(): void
    {
        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'header-token';

        $actual = (new HeaderRequestToken())->submitted($this->invocation(), new CsrfTokenField());

        $this->assertSame('header-token', $actual);
    }

    public function testResourceQueryRequestToken(): void
    {
        $resource = new FakeResource();
        $resource->uri = new FakeUri();
        $resource->uri->query = ['_csrf_token' => 'query-token'];

        $actual = (new ResourceQueryRequestToken())->submitted(new FakeInvocation($resource, 'onPost'), new CsrfTokenField());

        $this->assertSame('query-token', $actual);
    }

    public function testPostRequestToken(): void
    {
        $_POST['_csrf_token'] = 'post-token';

        $actual = (new PostRequestToken())->submitted($this->invocation(), new CsrfTokenField());

        $this->assertSame('post-token', $actual);
    }

    public function testCompositeRequestTokenPrecedence(): void
    {
        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'header-token';
        $_POST['_csrf_token'] = 'post-token';
        $resource = new FakeResource();
        $resource->uri = new FakeUri();
        $resource->uri->query = ['_csrf_token' => 'query-token'];

        $actual = $this->composite()->submitted(new FakeInvocation($resource, 'onPost'), new CsrfTokenField());

        $this->assertSame('header-token', $actual);
    }

    public function testCompositeRequestTokenFallsBackToResourceQueryThenPost(): void
    {
        $_POST['_csrf_token'] = 'post-token';
        $resource = new FakeResource();
        $resource->uri = new FakeUri();
        $resource->uri->query = ['_csrf_token' => 'query-token'];

        $actual = $this->composite()->submitted(new FakeInvocation($resource, 'onPost'), new CsrfTokenField());

        $this->assertSame('query-token', $actual);
    }

    public function testCompositeRequestTokenFallsBackToPost(): void
    {
        $_POST['_csrf_token'] = 'post-token';
        $resource = new FakeResource();
        $resource->uri = new FakeUri();
        $resource->uri->query = [];

        $actual = $this->composite()->submitted(new FakeInvocation($resource, 'onPost'), new CsrfTokenField());

        $this->assertSame('post-token', $actual);
    }

    private function invocation(): FakeInvocation
    {
        return new FakeInvocation(new FakeResource(), 'onPost');
    }

    private function composite(): CompositeRequestToken
    {
        return new CompositeRequestToken(new HeaderRequestToken(), new ResourceQueryRequestToken(), new PostRequestToken());
    }
}

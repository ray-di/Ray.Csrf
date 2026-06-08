<?php

declare(strict_types=1);

namespace Ray\Csrf;

use PHPUnit\Framework\TestCase;

use function session_id;
use function session_start;
use function session_status;
use function session_write_close;

use const PHP_SESSION_ACTIVE;

final class SessionCsrfTokenTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];

            return;
        }

        session_id('ray-csrf-test');
        session_start();
        $_SESSION = [];
    }

    public function testIssueVerifyAndClear(): void
    {
        $csrf = new SessionCsrfToken();
        $token = $csrf->issue();

        $this->assertNotSame('', $token);
        $this->assertTrue($csrf->verify($token));
        $this->assertFalse($csrf->verify('wrong-token'));

        $csrf->clear();
        $this->assertFalse($csrf->verify($token));
    }

    public function testIssueReturnsTheSameTokenWithinASession(): void
    {
        $csrf = new SessionCsrfToken();

        $this->assertSame($csrf->issue(), $csrf->issue());
    }

    public function testStartsSessionWhenInactive(): void
    {
        session_write_close();
        $this->assertNotSame(PHP_SESSION_ACTIVE, session_status());

        $token = (new SessionCsrfToken())->issue();

        $this->assertNotSame('', $token);
        $this->assertSame(PHP_SESSION_ACTIVE, session_status());
    }
}

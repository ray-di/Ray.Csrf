<?php

declare(strict_types=1);

namespace Ray\Csrf;

use PHPUnit\Framework\TestCase;

use function session_id;
use function session_start;
use function session_status;

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
}

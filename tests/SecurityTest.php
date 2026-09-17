<?php

use App\Support\Security;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox("Sécurité")]
final class SecurityTest extends TestCase {
    protected function setUp(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION = [];
        $_POST = [];
    }

    #[TestDox("Le jeton CSRF conserve une longueur sûre et reste stable pendant la session")]
    public function testCsrfTokenIsStableAndRandomLooking(): void {
        $token = Security::csrfToken();
        self::assertSame(64, strlen($token));
        self::assertSame($token, Security::csrfToken());
    }

    #[TestDox("La vérification CSRF accepte le bon jeton et refuse un jeton incorrect")]
    public function testCsrfVerificationRequiresMatchingToken(): void {
        $token = Security::csrfToken();
        $_POST['csrf_token'] = $token;
        self::assertTrue(Security::verifyCsrf());
        $_POST['csrf_token'] = $token . 'x';
        self::assertFalse(Security::verifyCsrf());
    }
}

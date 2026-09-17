<?php

use App\Support\Validation;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox("Validation")]
final class ValidationTest extends TestCase {
    #[TestDox("Un mot de passe respectant toutes les règles est accepté")]
    public function testPasswordPolicyAcceptsExpectedPassword(): void {
        self::assertSame([], Validation::passwordErrors('Pokejob8!'));
    }

    #[TestDox("Toutes les règles manquantes d’un mot de passe sont signalées")]
    public function testPasswordPolicyReportsEveryMissingRule(): void {
        self::assertCount(4, Validation::passwordErrors('abc'));
    }

    #[TestDox("Une adresse e-mail valide est normalisée")]
    public function testEmailIsNormalized(): void {
        self::assertSame('user@example.com', Validation::email(' User@Example.COM '));
    }

    #[TestDox("Une adresse e-mail invalide est refusée")]
    public function testInvalidEmailIsRejected(): void {
        self::assertNull(Validation::email('not-an-email'));
    }

    #[TestDox("Les dates valides sont acceptées et les dates impossibles sont refusées")]
    public function testDatesAreStrictlyValidated(): void {
        self::assertSame('2026-09-07', Validation::date('2026-09-07'));
        self::assertNull(Validation::date('2026-02-31'));
    }

    #[TestDox("Un mot de passe dépassant la capacité sûre de bcrypt est refusé")]
    public function testPasswordLongerThanBcryptLimitIsRejected(): void {
        self::assertContains('un mot de passe moins long', Validation::passwordErrors('A1!' . str_repeat('a', 70)));
    }

    #[TestDox("Seuls les liens HTTP et HTTPS sont acceptés")]
    public function testUrlsAreLimitedToHttpSchemes(): void {
        self::assertSame('https://example.com/offre', Validation::url('https://example.com/offre'));
        self::assertNull(Validation::url('ftp://example.com/archive'));
    }
}

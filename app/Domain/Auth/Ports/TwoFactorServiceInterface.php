<?php

declare(strict_types=1);

namespace Domain\Auth\Ports;

interface TwoFactorServiceInterface
{
    /**
     * Generate a new TOTP secret key.
     */
    public function generateSecret(): string;

    /**
     * Generate a QR code data URI for the given secret and user email.
     */
    public function generateQrCodeDataUri(string $secret, string $email): string;

    /**
     * Verify a TOTP code against the given secret.
     */
    public function verify(string $secret, string $code): bool;

    /**
     * Generate recovery codes.
     *
     * @return array{plain: array<string>, hashed: array<string>}
     */
    public function generateRecoveryCodes(int $count = 8): array;
}

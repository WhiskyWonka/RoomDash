<?php

declare(strict_types=1);

namespace Infrastructure\Auth\Adapters;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Domain\Auth\Ports\TwoFactorServiceInterface;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class Google2faService implements TwoFactorServiceInterface
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA;
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function generateQrCodeDataUri(string $secret, string $email): string
    {
        $appName = config('app.name', 'RoomDash');
        $qrCodeUrl = $this->google2fa->getQRCodeUrl($appName, $email, $secret);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd
        );
        $writer = new Writer($renderer);

        $svg = $writer->writeString($qrCodeUrl);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public function verify(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code, 1);
    }

    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        $hashedCodes = [];

        for ($i = 0; $i < $count; $i++) {
            $code = Str::upper(Str::random(4).'-'.Str::random(4));
            $codes[] = $code;
            $hashedCodes[] = hash('sha256', $code);
        }

        return [
            'plain' => $codes,
            'hashed' => $hashedCodes,
        ];
    }
}

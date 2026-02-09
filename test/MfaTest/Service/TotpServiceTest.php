<?php

declare(strict_types=1);

namespace MfaTest\Service;

use Mfa\Service\TotpService;
use PHPUnit\Framework\TestCase;

final class TotpServiceTest extends TestCase
{
    public function testVerifyCodeMatchesRfcVector(): void
    {
        $service = new TotpService(digits: 8, period: 30, window: 0);
        $secret  = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ'; // RFC 6238 test vector

        $result = $service->verifyCode($secret, '94287082', 59);

        $this->assertTrue($result);
    }

    public function testGenerateSecretReturnsBase32String(): void
    {
        $service = new TotpService();
        $secret  = $service->generateSecret();

        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
    }

    public function testProvisioningUriIsFormatted(): void
    {
        $service = new TotpService();
        $uri     = $service->getProvisioningUri('user@example.com', 'SECRET', 'Acme');

        $this->assertSame(
            'otpauth://totp/Acme%3Auser%40example.com?secret=SECRET&issuer=Acme',
            $uri
        );
    }

    public function testQrCodeUrlReturnsDataUri(): void
    {
        $service = new TotpService();
        $url     = $service->getQrCodeUrl('user@example.com', 'SECRET', 'Acme');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $url);
    }
}

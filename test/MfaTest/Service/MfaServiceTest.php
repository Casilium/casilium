<?php

declare(strict_types=1);

namespace MfaTest\Service;

use App\Encryption\Sodium;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mfa\Service\MfaService;
use Mfa\Service\TotpService;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use RuntimeException;
use User\Entity\User;
use UserAuthentication\Entity\IdentityInterface;

class MfaServiceTest extends TestCase
{
    use ProphecyTrait;

    private MfaService $mfaService;
    private ObjectProphecy $entityManager;
    private ObjectProphecy $repository;
    private ObjectProphecy $totpService;
    private ObjectProphecy $identity;
    private array $config;
    private string $encryptionKey;

    protected function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->repository    = $this->prophesize(EntityRepository::class);
        $this->totpService   = $this->prophesize(TotpService::class);
        $this->identity      = $this->prophesize(IdentityInterface::class);

        $this->entityManager->getRepository(User::class)
            ->willReturn($this->repository->reveal());

        $this->config = [
            'enabled' => true,
            'issuer'  => 'Test App',
        ];

        $this->encryptionKey = Sodium::generateKey();

        $this->mfaService = new MfaService(
            $this->entityManager->reveal(),
            $this->config,
            $this->totpService->reveal(),
            $this->encryptionKey
        );
    }

    public function testHasMfaReturnsTrueWhenUserHasMfaEnabled(): void
    {
        $this->identity->getId()->willReturn(123);

        $user = new User();
        $user->setMfaEnabled(true);

        $this->repository->find(123)->willReturn($user);

        $result = $this->mfaService->hasMfa($this->identity->reveal());

        $this->assertTrue($result);
    }

    public function testHasMfaReturnsFalseWhenUserHasMfaDisabled(): void
    {
        $this->identity->getId()->willReturn(123);

        $user = new User();
        $user->setMfaEnabled(false);

        $this->repository->find(123)->willReturn($user);

        $result = $this->mfaService->hasMfa($this->identity->reveal());

        $this->assertFalse($result);
    }

    public function testHasMfaReturnsFalseWhenUserNotFound(): void
    {
        $this->identity->getId()->willReturn(999);
        $this->repository->find(999)->willReturn(null);

        $result = $this->mfaService->hasMfa($this->identity->reveal());

        $this->assertFalse($result);
    }

    public function testEnableMfaUpdatesUserRecord(): void
    {
        $this->identity->getId()->willReturn(123);

        $user = new User();
        $user->setMfaEnabled(false);

        $this->repository->find(123)->willReturn($user);
        $this->entityManager->flush()->shouldBeCalled();

        $result = $this->mfaService->enableMfa($this->identity->reveal());

        $this->assertTrue($result);
        $this->assertTrue($user->isMfaEnabled());
    }

    public function testEnableMfaReturnsFalseWhenUserNotFound(): void
    {
        $this->identity->getId()->willReturn(123);
        $this->repository->find(123)->willReturn(null);

        $result = $this->mfaService->enableMfa($this->identity->reveal());

        $this->assertFalse($result);
    }

    public function testDisableMfaClearsSecretKeyAndDisablesMfa(): void
    {
        $this->identity->getId()->willReturn(123);

        $user = new User();
        $user->setMfaEnabled(true);
        $user->setSecretKey('some_key');

        $this->repository->find(123)->willReturn($user);
        $this->entityManager->flush()->shouldBeCalled();

        $result = $this->mfaService->disableMfa($this->identity->reveal());

        $this->assertTrue($result);
        $this->assertFalse($user->isMfaEnabled());
        $this->assertNull($user->getSecretKey());
    }

    public function testIsMfaEnabledReturnsTrueWhenConfigured(): void
    {
        $result = $this->mfaService->isMfaEnabled();
        $this->assertTrue($result);
    }

    public function testIsMfaEnabledReturnsFalseWhenDisabledInConfig(): void
    {
        $config     = ['enabled' => false, 'issuer' => 'Test App'];
        $mfaService = new MfaService(
            $this->entityManager->reveal(),
            $config,
            $this->totpService->reveal(),
            $this->encryptionKey
        );

        $result = $mfaService->isMfaEnabled();
        $this->assertFalse($result);
    }

    public function testIsMfaEnabledReturnsFalseWhenNotConfigured(): void
    {
        $config     = ['issuer' => 'Test App'];
        $mfaService = new MfaService(
            $this->entityManager->reveal(),
            $config,
            $this->totpService->reveal(),
            $this->encryptionKey
        );

        $result = $mfaService->isMfaEnabled();
        $this->assertFalse($result);
    }

    public function testGetSecretKeyReturnsDecryptedExistingKey(): void
    {
        $this->identity->getId()->willReturn(123);
        $plainKey = 'ABCDEFGHIJKLMNOP';

        $user = new User();
        $user->setSecretKey('enc:' . Sodium::encrypt($plainKey, $this->encryptionKey));

        $this->repository->find(123)->willReturn($user);

        $result = $this->mfaService->getSecretKey($this->identity->reveal());

        $this->assertEquals($plainKey, $result);
    }

    public function testGetSecretKeyGeneratesNewKeyWhenNoneExists(): void
    {
        $this->identity->getId()->willReturn(123);
        $generatedKey = 'NEWGENERATEDKEY123';

        $user = new User();
        $user->setSecretKey(null);

        $this->repository->find(123)->willReturn($user);
        $this->totpService->generateSecret()->willReturn($generatedKey);
        $this->entityManager->flush()->shouldBeCalled();

        $result = $this->mfaService->getSecretKey($this->identity->reveal());

        $this->assertEquals($generatedKey, $result);
    }

    public function testGetSecretKeyReturnsNullWhenUserNotFound(): void
    {
        $this->identity->getId()->willReturn(999);
        $this->repository->find(999)->willReturn(null);

        $result = $this->mfaService->getSecretKey($this->identity->reveal());

        $this->assertNull($result);
    }

    public function testGenerateSecretKeyDelegatesToTotpService(): void
    {
        $expectedSecret = 'GENERATED_SECRET';
        $this->totpService->generateSecret()->willReturn($expectedSecret);

        $result = $this->mfaService->generateSecretKey();

        $this->assertEquals($expectedSecret, $result);
    }

    public function testSaveSecretKeyEncryptsAndSaves(): void
    {
        $this->identity->getId()->willReturn(123);
        $secretKey = 'PLAIN_SECRET_KEY';

        $user = new User();
        $this->repository->find(123)->willReturn($user);
        $this->entityManager->flush()->shouldBeCalled();

        $result = $this->mfaService->saveSecretKey($this->identity->reveal(), $secretKey);

        $this->assertTrue($result);
        $this->assertStringStartsWith('enc:', $user->getSecretKey());
    }

    public function testIsValidCodeDelegatesToTotpService(): void
    {
        $secret = 'SECRET';
        $pin    = '123456';

        $this->totpService->verifyCode($secret, $pin)->willReturn(true);

        $result = $this->mfaService->isValidCode($secret, $pin);

        $this->assertTrue($result);
    }

    public function testGetIssuerReturnsConfiguredIssuer(): void
    {
        $result = $this->mfaService->getIssuer();
        $this->assertEquals('Test App', $result);
    }

    public function testGetIssuerThrowsExceptionWhenNotConfigured(): void
    {
        $config     = ['enabled' => true];
        $mfaService = new MfaService(
            $this->entityManager->reveal(),
            $config,
            $this->totpService->reveal(),
            $this->encryptionKey
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Issuer not found!');

        $mfaService->getIssuer();
    }

    public function testGetQrCodeUrlDelegatesToTotpService(): void
    {
        $email       = 'test@example.com';
        $key         = 'SECRET_KEY_123';
        $expectedUrl = 'data:image/svg+xml;base64,abc123';

        $this->totpService->getQrCodeUrl($email, $key, 'Test App')
            ->willReturn($expectedUrl);

        $result = $this->mfaService->getQrCodeUrl($email, $key);

        $this->assertEquals($expectedUrl, $result);
    }

    /**
     * @dataProvider booleanConfigProvider
     */
    public function testIsMfaEnabledWithVariousConfigurations(mixed $configValue, bool $expected): void
    {
        $config     = ['enabled' => $configValue, 'issuer' => 'Test'];
        $mfaService = new MfaService(
            $this->entityManager->reveal(),
            $config,
            $this->totpService->reveal(),
            $this->encryptionKey
        );

        $this->assertEquals($expected, $mfaService->isMfaEnabled());
    }

    public static function booleanConfigProvider(): array
    {
        return [
            'true boolean'   => [true, true],
            'false boolean'  => [false, false],
            'truthy string'  => ['1', true],
            'falsy string'   => ['0', false],
            'truthy integer' => [1, true],
            'falsy integer'  => [0, false],
        ];
    }
}

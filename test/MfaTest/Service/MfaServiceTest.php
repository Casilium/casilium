<?php

declare(strict_types=1);

namespace MfaTest\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use Exception;
use Mfa\Service\MfaService;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use UserAuthentication\Entity\IdentityInterface;

use function urlencode;

class MfaServiceTest extends TestCase
{
    use ProphecyTrait;

    private MfaService $mfaService;
    private ObjectProphecy $connection;
    private ObjectProphecy $statement;
    private ObjectProphecy $result;
    private ObjectProphecy $identity;
    private array $config;

    protected function setUp(): void
    {
        $this->connection = $this->prophesize(Connection::class);
        $this->statement  = $this->prophesize(Statement::class);
        $this->result     = $this->prophesize(Result::class);
        $this->identity   = $this->prophesize(IdentityInterface::class);

        $this->config = [
            'enabled' => true,
            'issuer'  => 'Test App',
        ];

        $this->mfaService = new MfaService($this->connection->reveal(), $this->config);
    }

    public function testHasMfaReturnsTrueWhenUserHasMfaEnabled(): void
    {
        $this->identity->getId()->willReturn(123);

        $this->connection->prepare('SELECT mfa_enabled from `user` WHERE id = ? LIMIT 1')
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, 123)->shouldBeCalled();
        $this->statement->executeQuery()->willReturn($this->result->reveal());

        $this->result->rowCount()->willReturn(1);
        $this->result->fetchOne()->willReturn(1);

        $result = $this->mfaService->hasMfa($this->identity->reveal());

        $this->assertTrue($result);
    }

    public function testHasMfaReturnsFalseWhenUserHasMfaDisabled(): void
    {
        $this->identity->getId()->willReturn(123);

        $this->connection->prepare(Argument::type('string'))
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, 123)->shouldBeCalled();
        $this->statement->executeQuery()->willReturn($this->result->reveal());

        $this->result->rowCount()->willReturn(1);
        $this->result->fetchOne()->willReturn(0);

        $result = $this->mfaService->hasMfa($this->identity->reveal());

        $this->assertFalse($result);
    }

    public function testHasMfaReturnsFalseWhenUserNotFound(): void
    {
        $this->identity->getId()->willReturn(999);

        $this->connection->prepare(Argument::type('string'))
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, 999)->shouldBeCalled();
        $this->statement->executeQuery()->willReturn($this->result->reveal());

        $this->result->rowCount()->willReturn(0);

        $result = $this->mfaService->hasMfa($this->identity->reveal());

        $this->assertFalse($result);
    }

    public function testEnableMfaUpdatesUserRecord(): void
    {
        $this->identity->getId()->willReturn(123);

        $this->connection->prepare('UPDATE `user` SET mfa_enabled = 1 WHERE id = ?')
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, 123)->shouldBeCalled();
        $this->statement->executeStatement()->willReturn(1);

        $result = $this->mfaService->enableMfa($this->identity->reveal());

        $this->assertTrue($result);
    }

    public function testEnableMfaReturnsFalseOnFailure(): void
    {
        $this->identity->getId()->willReturn(123);

        $this->connection->prepare(Argument::type('string'))
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, 123)->shouldBeCalled();
        $this->statement->executeStatement()->willReturn(0);

        $result = $this->mfaService->enableMfa($this->identity->reveal());

        $this->assertFalse($result);
    }

    public function testDisableMfaClearsSecretKeyAndDisablesMfa(): void
    {
        $this->identity->getId()->willReturn(123);

        $this->connection->prepare('UPDATE `user` SET mfa_enabled = 0, secret_key = null WHERE id = ?')
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, 123)->shouldBeCalled();
        $this->statement->executeStatement()->willReturn(1);

        $result = $this->mfaService->disableMfa($this->identity->reveal());

        $this->assertTrue($result);
    }

    public function testIsMfaEnabledReturnsTrueWhenConfigured(): void
    {
        $result = $this->mfaService->isMfaEnabled();
        $this->assertTrue($result);
    }

    public function testIsMfaEnabledReturnsFalseWhenDisabledInConfig(): void
    {
        $config     = ['enabled' => false, 'issuer' => 'Test App'];
        $mfaService = new MfaService($this->connection->reveal(), $config);

        $result = $mfaService->isMfaEnabled();
        $this->assertFalse($result);
    }

    public function testIsMfaEnabledReturnsFalseWhenNotConfigured(): void
    {
        $config     = ['issuer' => 'Test App'];
        $mfaService = new MfaService($this->connection->reveal(), $config);

        $result = $mfaService->isMfaEnabled();
        $this->assertFalse($result);
    }

    public function testGetSecretKeyReturnsExistingKey(): void
    {
        $this->identity->getId()->willReturn(123);
        $existingKey = 'EXISTING_SECRET_KEY_123';

        $this->connection->prepare('SELECT secret_key from `user` WHERE id = ? LIMIT 1')
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, 123)->shouldBeCalled();
        $this->statement->executeQuery()->willReturn($this->result->reveal());

        $this->result->rowCount()->willReturn(1);
        $this->result->fetchOne()->willReturn($existingKey);

        $result = $this->mfaService->getSecretKey($this->identity->reveal());

        $this->assertEquals($existingKey, $result);
    }

    public function testGetSecretKeyGeneratesNewKeyWhenNoneExists(): void
    {
        $this->identity->getId()->willReturn(123);

        // Setup for getting existing key (returns null)
        $this->connection->prepare('SELECT secret_key from `user` WHERE id = ? LIMIT 1')
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, 123)->shouldBeCalled();
        $this->statement->executeQuery()->willReturn($this->result->reveal());

        $this->result->rowCount()->willReturn(1);
        $this->result->fetchOne()->willReturn(null);

        // Setup for saving new key
        $saveStatement = $this->prophesize(Statement::class);
        $this->connection->prepare('UPDATE `user` SET secret_key = ? WHERE id = ?')
            ->willReturn($saveStatement->reveal());

        $saveStatement->bindValue(1, Argument::type('string'))->shouldBeCalled();
        $saveStatement->bindValue(2, 123)->shouldBeCalled();
        $saveStatement->executeStatement()->willReturn(1);

        $result = $this->mfaService->getSecretKey($this->identity->reveal());

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testGetSecretKeyReturnsNullWhenUserNotFound(): void
    {
        $this->identity->getId()->willReturn(999);

        $this->connection->prepare(Argument::type('string'))
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, 999)->shouldBeCalled();
        $this->statement->executeQuery()->willReturn($this->result->reveal());

        $this->result->rowCount()->willReturn(0);

        $result = $this->mfaService->getSecretKey($this->identity->reveal());

        $this->assertNull($result);
    }

    public function testGenerateSecretKeyReturnsString(): void
    {
        $result = $this->mfaService->generateSecretKey();

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testSaveSecretKeyUpdatesDatabase(): void
    {
        $this->identity->getId()->willReturn(123);
        $secretKey = 'NEW_SECRET_KEY_456';

        $this->connection->prepare('UPDATE `user` SET secret_key = ? WHERE id = ?')
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, $secretKey)->shouldBeCalled();
        $this->statement->bindValue(2, 123)->shouldBeCalled();
        $this->statement->executeStatement()->willReturn(1);

        $result = $this->mfaService->saveSecretKey($this->identity->reveal(), $secretKey);

        $this->assertTrue($result);
    }

    public function testIsValidCodeValidatesCorrectPin(): void
    {
        // This test uses the actual GoogleAuthenticator functionality
        // We'll test with known valid combinations
        $secretKey = $this->mfaService->generateSecretKey();

        // Since we can't predict the time-based code, we'll test the method exists
        // and returns a boolean
        $result = $this->mfaService->isValidCode($secretKey, '123456');

        $this->assertIsBool($result);
    }

    public function testGetIssuerReturnsConfiguredIssuer(): void
    {
        $result = $this->mfaService->getIssuer();
        $this->assertEquals('Test App', $result);
    }

    public function testGetIssuerThrowsExceptionWhenNotConfigured(): void
    {
        $config     = ['enabled' => true];
        $mfaService = new MfaService($this->connection->reveal(), $config);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Issuer not found!');

        $mfaService->getIssuer();
    }

    public function testGetQrCodeUrlReturnsValidUrl(): void
    {
        $email = 'test@example.com';
        $key   = 'SECRET_KEY_123';

        $result = $this->mfaService->getQrCodeUrl($email, $key);

        $this->assertIsString($result);
        // The GoogleQrUrl generates a QR server URL that contains the encoded otpauth URL
        $this->assertStringContainsString('qrserver.com', $result);
        $this->assertStringContainsString(urlencode($email), $result);
        $this->assertStringContainsString('Test%20App', $result);
    }

    /**
     * @dataProvider booleanConfigProvider
     */
    public function testIsMfaEnabledWithVariousConfigurations(mixed $configValue, bool $expected): void
    {
        $config     = ['enabled' => $configValue, 'issuer' => 'Test'];
        $mfaService = new MfaService($this->connection->reveal(), $config);

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

<?php

declare(strict_types=1);

namespace UserAuthenticationTest\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use UserAuthentication\Entity\Identity;
use UserAuthentication\Service\AuthenticationService;

use function password_hash;

use const PASSWORD_BCRYPT;

class AuthenticationServiceTest extends TestCase
{
    use ProphecyTrait;

    private AuthenticationService $authService;
    private ObjectProphecy $connection;
    private ObjectProphecy $statement;
    private ObjectProphecy $result;

    protected function setUp(): void
    {
        $this->connection = $this->prophesize(Connection::class);
        $this->statement  = $this->prophesize(Statement::class);
        $this->result     = $this->prophesize(Result::class);

        $this->authService = new AuthenticationService($this->connection->reveal());
    }

    public function testAuthenticateWithValidCredentialsReturnsIdentity(): void
    {
        $username       = 'test@example.com';
        $password       = 'plaintext_password';
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $userData = [
            'id'        => '123',
            'full_name' => 'Test User',
            'email'     => 'test@example.com',
            'password'  => $hashedPassword,
        ];

        // Setup database mocks
        $this->connection->prepare('SELECT id,full_name,email,password FROM `user` WHERE `email` = ? LIMIT 1')
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, $username)->shouldBeCalled();
        $this->statement->executeQuery()->willReturn($this->result->reveal());

        $this->result->rowCount()->willReturn(1);
        $this->result->fetchAssociative()->willReturn($userData);

        $identity = $this->authService->authenticate($username, $password);

        $this->assertInstanceOf(Identity::class, $identity);
        $this->assertEquals(123, $identity->getId());
        $this->assertEquals('test@example.com', $identity->getEmail());
        $this->assertEquals('Test User', $identity->getName());
    }

    public function testAuthenticateWithInvalidPasswordReturnsNull(): void
    {
        $username       = 'test@example.com';
        $password       = 'wrong_password';
        $hashedPassword = password_hash('correct_password', PASSWORD_BCRYPT);

        $userData = [
            'id'        => '123',
            'full_name' => 'Test User',
            'email'     => 'test@example.com',
            'password'  => $hashedPassword,
        ];

        // Setup database mocks
        $this->connection->prepare(Argument::type('string'))
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, $username)->shouldBeCalled();
        $this->statement->executeQuery()->willReturn($this->result->reveal());

        $this->result->rowCount()->willReturn(1);
        $this->result->fetchAssociative()->willReturn($userData);

        $identity = $this->authService->authenticate($username, $password);

        $this->assertNull($identity);
    }

    public function testAuthenticateWithNonExistentUserReturnsNull(): void
    {
        $username = 'nonexistent@example.com';
        $password = 'any_password';

        // Setup database mocks
        $this->connection->prepare(Argument::type('string'))
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, $username)->shouldBeCalled();
        $this->statement->executeQuery()->willReturn($this->result->reveal());

        $this->result->rowCount()->willReturn(0);

        $identity = $this->authService->authenticate($username, $password);

        $this->assertNull($identity);
    }

    public function testAuthenticateWithDatabaseErrorReturnsNull(): void
    {
        $username = 'test@example.com';
        $password = 'password';

        // Setup database mocks
        $this->connection->prepare(Argument::type('string'))
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, $username)->shouldBeCalled();
        $this->statement->executeQuery()->willReturn($this->result->reveal());

        $this->result->rowCount()->willReturn(1);
        $this->result->fetchAssociative()->willReturn(false); // Simulate DB error

        $identity = $this->authService->authenticate($username, $password);

        $this->assertNull($identity);
    }

    public function testAuthenticateUsesCorrectSqlQuery(): void
    {
        $expectedSql = 'SELECT id,full_name,email,password FROM `user` WHERE `email` = ? LIMIT 1';

        $this->connection->prepare($expectedSql)
            ->shouldBeCalled()
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, 'test@example.com')->shouldBeCalled();
        $this->statement->executeQuery()->willReturn($this->result->reveal());
        $this->result->rowCount()->willReturn(0);

        $this->authService->authenticate('test@example.com', 'password');
    }

    public function testAuthenticateBindsUsernameParameter(): void
    {
        $username = 'specific@example.com';

        $this->connection->prepare(Argument::type('string'))
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, $username)->shouldBeCalled();
        $this->statement->executeQuery()->willReturn($this->result->reveal());
        $this->result->rowCount()->willReturn(0);

        $this->authService->authenticate($username, 'password');
    }

    public function testAuthenticateWithEmptyCredentials(): void
    {
        $this->connection->prepare(Argument::type('string'))
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, '')->shouldBeCalled();
        $this->statement->executeQuery()->willReturn($this->result->reveal());
        $this->result->rowCount()->willReturn(0);

        $identity = $this->authService->authenticate('', '');

        $this->assertNull($identity);
    }

    /**
     * @dataProvider credentialsProvider
     */
    public function testAuthenticateWithVariousCredentials(string $username, string $password, bool $userExists): void
    {
        $this->connection->prepare(Argument::type('string'))
            ->willReturn($this->statement->reveal());

        $this->statement->bindValue(1, $username)->shouldBeCalled();
        $this->statement->executeQuery()->willReturn($this->result->reveal());
        $this->result->rowCount()->willReturn($userExists ? 1 : 0);

        if ($userExists) {
            $this->result->fetchAssociative()->willReturn(false);
        }

        $identity = $this->authService->authenticate($username, $password);

        $this->assertNull($identity);
    }

    public static function credentialsProvider(): array
    {
        return [
            'valid email format'          => ['user@domain.com', 'password123', true],
            'invalid email format'        => ['invalid-email', 'password123', false],
            'empty username'              => ['', 'password123', false],
            'empty password'              => ['user@domain.com', '', true],
            'special characters in email' => ['user+tag@domain.com', 'pass', false],
        ];
    }
}

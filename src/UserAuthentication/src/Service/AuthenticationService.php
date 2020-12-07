<?php

declare(strict_types=1);

namespace UserAuthentication\Service;

use Doctrine\DBAL\Connection;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use UserAuthentication\Entity\Identity;
use UserAuthentication\Entity\IdentityInterface;
use function is_array;
use function password_verify;

class AuthenticationService
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function authenticate(string $username, string $password): ?IdentityInterface
    {
        // prepare SQL statement to fetch user from database
        $sql  = 'SELECT id,full_name,email,password FROM `user` WHERE `email` = ? LIMIT 1';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $username);

        if ($stmt->execute()) {
            if ($result = $stmt->fetchAssociative()) {
                if (password_verify($password, $result['password'])) {
                    $identity = new Identity();
                    $identity->setId((int) $result['id']);
                    $identity->setEmail($result['email']);
                    $identity->setName($result['full_name']);
                    return $identity;
                }
            }
        }

        return null;
    }

    private function createIdentityFromArray(array $identityInfo): ?IdentityInterface
    {
        if (! is_array($identityInfo) || ! isset($identityInfo['email'])) {
            return null;
        }

        $identity = new Identity();
        $identity->setId($identityInfo['id']);
        $identity->setEmail($identityInfo['email']);
        $identity->setName($identityInfo['full_name']);

        return $identity;
    }
}

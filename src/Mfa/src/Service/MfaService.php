<?php

declare(strict_types=1);

namespace Mfa\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;
use UserAuthentication\Entity\IdentityInterface;

class MfaService
{
    protected GoogleAuthenticator $authenticator;

    protected Connection $connection;

    /** @var array */
    protected array $config;

    public function __construct(Connection $connection, array $config)
    {
        $this->authenticator = new GoogleAuthenticator();
        $this->connection    = $connection;
        $this->config        = $config;
    }

    /**
     * Checks if user has MFA enabled
     *
     * @param IdentityInterface $user User to check if MFA is enabled
     * @return false|mixed false if user not found, 0 if disable or 1 if enabled
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function hasMfa(IdentityInterface $user): bool
    {
        $sql  = 'SELECT mfa_enabled from `user` WHERE id = ? LIMIT 1';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $user->getId());
        $result = $stmt->executeQuery();

        if ($result->rowCount()) {
            return (bool) $result->fetchOne();
        }

        return false;
    }

    /**
     * Enable MFA for user
     *
     * @param IdentityInterface $user User to check if MFA is enabled
     * @return false|mixed true if successful or false
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function enableMfa(IdentityInterface $user): bool
    {
        $sql  = 'UPDATE `user` SET mfa_enabled = 1 WHERE id = ?';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $user->getId());
        return (bool) $stmt->executeStatement();
    }

    /**
     * Enable MFA for user
     *
     * @param IdentityInterface $user User to check if MFA is enabled
     * @return false|mixed false if user not found, 0 if disable or 1 if enabled
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function disableMfa(IdentityInterface $user)
    {
        // prepare SQL statement and bind values
        $sql  = 'UPDATE `user` SET mfa_enabled = 0, secret_key = null WHERE id = ?';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $user->getId());
        return (bool) $stmt->executeStatement();
    }

    /**
     * Checks if MFA is enabled within application
     *
     * @return bool true if enabled, or false
     */
    public function isMfaEnabled(): bool
    {
        return isset($this->config['enabled']) && (bool) $this->config['enabled'];
    }

    /**
     * Retrieve user's mfa secret key
     *
     * @param IdentityInterface $user user object
     * @return string|null secret key or null
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSecretKey(IdentityInterface $user): ?string
    {
        // prepare sql statement
        $sql  = 'SELECT secret_key from `user` WHERE id = ? LIMIT 1';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $user->getId());
        $result = $stmt->executeQuery();

        if ($result->rowCount()) {
            if (! $secretKey = $result->fetchOne()) {
                // no secret key? Generate and save
                $key = $this->generateSecretKey();
                $this->saveSecretKey($user, $key);
                return $key;
            }

            // return saved secret key
            return $secretKey;
        }

        return null;
    }

    /**
     * Generate secret key
     *
     * @return string secret key
     */
    public function generateSecretKey(): string
    {
        return $this->authenticator->generateSecret();
    }

    /**
     * @param IdentityInterface $user user object
     * @param string $key secret key
     * @return bool true if saved or false
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function saveSecretKey(IdentityInterface $user, string $key): bool
    {
        // prepare SQL statement and bind values
        $sql  = 'UPDATE `user` SET secret_key = ? WHERE id = ?';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $key);
        $stmt->bindValue(2, $user->getId());
        return (bool) $stmt->executeStatement();
    }

    /**
     * Check if MFA entry is successful
     *
     * @param string $code secret key
     * @param string $pin time coded pin
     * @return bool true if valid, or false
     */
    public function isValidCode(string $code, string $pin): bool
    {
        return $this->authenticator->checkCode($code, $pin);
    }

    /**
     * Return issuer
     *
     * @return string issuer
     * @throws \Exception
     */
    public function getIssuer(): string
    {
        if (isset($this->config['issuer'])) {
            return $this->config['issuer'];
        }

        throw new \Exception('Issuer not found!');
    }

    /**
     * Generate Google QR Code url
     *
     * @param string $email email of user
     * @param string $key user's key
     * @return string QR Code URL
     */
    public function getQrCodeUrl(string $email, string $key): string
    {
        return GoogleQrUrl::generate($email, $key, $this->getIssuer());
    }
}

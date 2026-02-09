<?php

declare(strict_types=1);

namespace Mfa\Service;

use App\Encryption\Sodium;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use User\Entity\User;
use UserAuthentication\Entity\IdentityInterface;

use function str_starts_with;
use function strlen;
use function substr;

class MfaService
{
    private const ENCRYPTED_PREFIX = 'enc:';

    private EntityManagerInterface $entityManager;
    private TotpService $totpService;
    private array $config;
    private string $encryptionKey;

    public function __construct(
        EntityManagerInterface $entityManager,
        array $config,
        TotpService $totpService,
        string $encryptionKey
    ) {
        $this->entityManager = $entityManager;
        $this->config        = $config;
        $this->totpService   = $totpService;
        $this->encryptionKey = $encryptionKey;
    }

    /**
     * Checks if user has MFA enabled
     */
    public function hasMfa(IdentityInterface $identity): bool
    {
        $user = $this->findUser($identity->getId());
        if ($user === null) {
            return false;
        }

        return $user->isMfaEnabled();
    }

    /**
     * Enable MFA for user
     */
    public function enableMfa(IdentityInterface $identity): bool
    {
        $user = $this->findUser($identity->getId());
        if ($user === null) {
            return false;
        }

        $user->setMfaEnabled(true);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Disable MFA for user
     */
    public function disableMfa(IdentityInterface $identity): bool
    {
        $user = $this->findUser($identity->getId());
        if ($user === null) {
            return false;
        }

        $user->setMfaEnabled(false);
        $user->setSecretKey(null);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Checks if MFA is enabled within application
     */
    public function isMfaEnabled(): bool
    {
        return isset($this->config['enabled']) && (bool) $this->config['enabled'];
    }

    /**
     * Retrieve user's mfa secret key (decrypted)
     */
    public function getSecretKey(IdentityInterface $identity): ?string
    {
        $user = $this->findUser($identity->getId());
        if ($user === null) {
            return null;
        }

        $secretKey = $user->getSecretKey();
        if ($secretKey === null || $secretKey === '') {
            // No secret key? Generate and save
            $key = $this->generateSecretKey();
            $this->saveSecretKey($identity, $key);
            return $key;
        }

        return $this->decryptSecret($secretKey);
    }

    /**
     * Generate secret key
     */
    public function generateSecretKey(): string
    {
        return $this->totpService->generateSecret();
    }

    /**
     * Save secret key (encrypted)
     */
    public function saveSecretKey(IdentityInterface $identity, string $key): bool
    {
        $user = $this->findUser($identity->getId());
        if ($user === null) {
            return false;
        }

        $user->setSecretKey($this->encryptSecret($key));
        $this->entityManager->flush();

        return true;
    }

    /**
     * Check if MFA code is valid
     */
    public function isValidCode(string $secret, string $pin): bool
    {
        return $this->totpService->verifyCode($secret, $pin);
    }

    /**
     * Return issuer
     */
    public function getIssuer(): string
    {
        if (isset($this->config['issuer'])) {
            return $this->config['issuer'];
        }

        throw new RuntimeException('Issuer not found!');
    }

    /**
     * Generate QR Code URL
     */
    public function getQrCodeUrl(string $email, string $key): string
    {
        return $this->totpService->getQrCodeUrl($email, $key, $this->getIssuer());
    }

    private function findUser(int $userId): ?User
    {
        return $this->entityManager->getRepository(User::class)->find($userId);
    }

    private function encryptSecret(string $plain): string
    {
        return self::ENCRYPTED_PREFIX . Sodium::encrypt($plain, $this->encryptionKey);
    }

    private function decryptSecret(string $stored): string
    {
        if (! $this->isEncrypted($stored)) {
            return $stored;
        }

        $cipher = substr($stored, strlen(self::ENCRYPTED_PREFIX));
        return Sodium::decrypt($cipher, $this->encryptionKey);
    }

    private function isEncrypted(string $stored): bool
    {
        return str_starts_with($stored, self::ENCRYPTED_PREFIX);
    }
}

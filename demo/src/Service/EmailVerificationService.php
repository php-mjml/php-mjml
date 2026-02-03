<?php

declare(strict_types=1);

/*
 * This file is part of the PHP-MJML package.
 *
 * (c) David Gorges
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Constant\SessionKey;
use Symfony\Component\HttpFoundation\RequestStack;

class EmailVerificationService
{
    private const int TOKEN_LENGTH = 32;
    private const int TOKEN_TTL_SECONDS = 86400; // 24 hours

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function createVerification(string $email): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH / 2));
        $expiresAt = time() + self::TOKEN_TTL_SECONDS;

        $session = $this->requestStack->getSession();
        $session->set(SessionKey::PENDING_VERIFICATION, [
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);

        return $token;
    }

    public function validateToken(string $token): ?string
    {
        $session = $this->requestStack->getSession();
        $pending = $session->get(SessionKey::PENDING_VERIFICATION);

        if (null === $pending) {
            return null;
        }

        if ($pending['token'] !== $token) {
            return null;
        }

        if ($pending['expires_at'] < time()) {
            $session->remove(SessionKey::PENDING_VERIFICATION);

            return null;
        }

        return $pending['email'];
    }

    public function isVerified(string $email): bool
    {
        $verifiedEmails = $this->getVerifiedEmails();

        return isset($verifiedEmails[$email]);
    }

    public function markVerified(string $email): void
    {
        $session = $this->requestStack->getSession();
        $verifiedEmails = $this->getVerifiedEmails();

        $verifiedEmails[$email] = time();

        $session->set(SessionKey::VERIFIED_EMAILS, $verifiedEmails);
        $session->remove(SessionKey::PENDING_VERIFICATION);
    }

    /**
     * @return array<string, int> Map of email => verified timestamp
     */
    public function getVerifiedEmails(): array
    {
        $session = $this->requestStack->getSession();

        return $session->get(SessionKey::VERIFIED_EMAILS, []);
    }
}

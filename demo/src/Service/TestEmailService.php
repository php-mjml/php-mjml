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

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TestEmailService
{
    private const string FROM_ADDRESS = 'noreply@php-mjml.dev';
    private const string FROM_NAME = 'PHP-MJML Demo';

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function sendVerificationEmail(string $emailAddress, string $token): void
    {
        $verifyUrl = $this->urlGenerator->generate(
            'email_verify',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $html = $this->buildVerificationEmailHtml($verifyUrl);

        $email = (new Email())
            ->from(sprintf('%s <%s>', self::FROM_NAME, self::FROM_ADDRESS))
            ->to($emailAddress)
            ->subject('Verify your email for PHP-MJML Demo')
            ->html($html)
            ->text(sprintf(
                "Please verify your email address by clicking this link:\n\n%s\n\nThis link will expire in 24 hours.",
                $verifyUrl
            ));

        $this->mailer->send($email);
    }

    public function sendTestEmail(string $emailAddress, string $htmlContent): void
    {
        $email = (new Email())
            ->from(sprintf('%s <%s>', self::FROM_NAME, self::FROM_ADDRESS))
            ->to($emailAddress)
            ->subject('Test Email from PHP-MJML Demo')
            ->html($htmlContent);

        $this->mailer->send($email);
    }

    private function buildVerificationEmailHtml(string $verifyUrl): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f3f4f6;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #4e46e5 0%, #7c3aed 100%); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">PHP-MJML Demo</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #1f2937; margin: 0 0 20px; font-size: 20px;">Verify Your Email</h2>
                            <p style="color: #4b5563; margin: 0 0 24px; line-height: 1.6;">
                                Please click the button below to verify your email address and enable test email functionality.
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="{$verifyUrl}" style="display: inline-block; background-color: #4e46e5; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-weight: 500; font-size: 16px;">
                                            Verify Email Address
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="color: #6b7280; margin: 24px 0 0; font-size: 14px; line-height: 1.6;">
                                This link will expire in 24 hours. If you didn't request this, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 30px; text-align: center;">
                            <p style="color: #6b7280; margin: 0; font-size: 12px;">
                                Sent from the PHP-MJML Demo application
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}

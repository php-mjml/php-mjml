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

namespace App\Controller;

use App\Service\EmailVerificationService;
use App\Service\TestEmailService;
use PhpMjml\Component\Registry;
use PhpMjml\Parser\MjmlParser;
use PhpMjml\Preset\CorePreset;
use PhpMjml\Renderer\Mjml2Html;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

class DemoController extends AbstractController
{
    #[Route('/', name: 'demo_index')]
    public function index(): Response
    {
        return $this->render('demo/index.html.twig');
    }

    #[Route('/render', name: 'demo_render', methods: ['POST'])]
    public function renderMjml(Request $request): Response
    {
        $mjml = $request->request->getString('mjml') ?: $this->getDefaultMjml();

        $renderer = $this->createRenderer();
        $result = $renderer->render($mjml);

        return new Response($result->html);
    }

    #[Route('/preview', name: 'demo_preview')]
    public function preview(): Response
    {
        $mjml = $this->getDefaultMjml();

        $renderer = $this->createRenderer();
        $result = $renderer->render($mjml);

        return new Response($result->html);
    }

    private function getDefaultMjml(): string
    {
        return <<<'MJML'
<mjml>
  <mj-head>
    <mj-title>PHP-MJML Demo</mj-title>
    <mj-preview>Welcome to PHP-MJML</mj-preview>
  </mj-head>
  <mj-body>
    <mj-section background-color="#4e46e5">
      <mj-column>
        <mj-text font-size="28px" color="#ffffff" align="center" font-weight="bold">
          PHP-MJML Demo
        </mj-text>
        <mj-text font-size="16px" color="#e0e7ff" align="center">
          Native PHP port of the MJML email framework
        </mj-text>
      </mj-column>
    </mj-section>
    <mj-section background-color="#ffffff">
      <mj-column>
        <mj-text font-size="18px" color="#1f2937" padding-top="30px">
          Welcome to PHP-MJML!
        </mj-text>
        <mj-text color="#4b5563">
          This email was rendered using the PHP-MJML library, a native PHP implementation
          of the MJML email templating framework. No Node.js required!
        </mj-text>
        <mj-button background-color="#4e46e5" href="https://github.com/php-mjml/php-mjml">
          View on GitHub
        </mj-button>
      </mj-column>
    </mj-section>
    <mj-section background-color="#f3f4f6">
      <mj-column>
        <mj-text font-size="12px" color="#6b7280" align="center">
          Built with PHP-MJML
        </mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;
    }

    #[Route('/email/status', name: 'email_status', methods: ['GET'])]
    public function emailStatus(EmailVerificationService $verificationService): JsonResponse
    {
        $verifiedEmails = $verificationService->getVerifiedEmails();

        return new JsonResponse([
            'verified_emails' => array_keys($verifiedEmails),
        ]);
    }

    #[Route('/email/request-verification', name: 'email_request_verification', methods: ['POST'])]
    public function requestVerification(
        Request $request,
        EmailVerificationService $verificationService,
        TestEmailService $emailService,
        #[Autowire(service: 'limiter.verification_request')]
        RateLimiterFactory $verificationRequestLimiter,
    ): JsonResponse {
        $email = $request->request->getString('email');

        if ('' === $email || !filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Invalid email address'], Response::HTTP_BAD_REQUEST);
        }

        if ($verificationService->isVerified($email)) {
            return new JsonResponse(['message' => 'Email already verified']);
        }

        $limiter = $verificationRequestLimiter->create($request->getClientIp().'-'.hash('sha256', $email));
        if (!$limiter->consume()->isAccepted()) {
            return new JsonResponse(
                ['error' => 'Too many verification requests. Please try again later.'],
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        $token = $verificationService->createVerification($email);
        $emailService->sendVerificationEmail($email, $token);

        return new JsonResponse(['message' => 'Verification email sent']);
    }

    #[Route('/email/verify/{token}', name: 'email_verify', methods: ['GET'])]
    public function verifyEmail(
        string $token,
        EmailVerificationService $verificationService,
    ): Response {
        $email = $verificationService->validateToken($token);

        if (null === $email) {
            return $this->render('demo/verify_error.html.twig', [
                'message' => 'Invalid or expired verification link.',
            ]);
        }

        $verificationService->markVerified($email);

        return $this->render('demo/verify_success.html.twig', [
            'email' => $email,
        ]);
    }

    #[Route('/email/send', name: 'email_send', methods: ['POST'])]
    public function sendTestEmail(
        Request $request,
        EmailVerificationService $verificationService,
        TestEmailService $emailService,
        #[Autowire(service: 'limiter.test_email_send')]
        RateLimiterFactory $testEmailSendLimiter,
    ): JsonResponse {
        $email = $request->request->getString('email');
        $htmlContent = $request->request->getString('html');

        if ('' === $email || !filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Invalid email address'], Response::HTTP_BAD_REQUEST);
        }

        if (!$verificationService->isVerified($email)) {
            return new JsonResponse(
                ['error' => 'Email address not verified'],
                Response::HTTP_FORBIDDEN
            );
        }

        if ('' === $htmlContent) {
            return new JsonResponse(['error' => 'No HTML content provided'], Response::HTTP_BAD_REQUEST);
        }

        $limiter = $testEmailSendLimiter->create($request->getSession()->getId());
        if (!$limiter->consume()->isAccepted()) {
            return new JsonResponse(
                ['error' => 'Too many test emails sent. Please try again later.'],
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        $emailService->sendTestEmail($email, $htmlContent);

        return new JsonResponse(['message' => 'Test email sent successfully']);
    }

    private function createRenderer(): Mjml2Html
    {
        $registry = new Registry();
        $registry->registerMany(CorePreset::getComponents());

        return new Mjml2Html($registry, new MjmlParser(registry: $registry));
    }
}

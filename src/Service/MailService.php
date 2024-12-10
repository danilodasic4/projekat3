<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class MailService
{
    private MailerInterface $mailer;
    private Environment $twig;
    private LoggerInterface $logger;
    private string $apiHost;

    public function __construct(MailerInterface $mailer, Environment $twig, LoggerInterface $logger, RequestStack $requestStack)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;

        $this->apiHost = $_ENV['API_HOST'] ?? 'http://localhost'; 
    }

    public function sendExpiringRegistrationEmail(string $email, array $cars): void
    {
        $emailBody = $this->twig->render('emails/registration_expiring.html.twig', [
            'cars' => $cars,
        ]);

        $emailMessage = (new Email())
            ->from('noreply@yourdomain.com')
            ->to($email)
            ->subject('Car Registration Expiring Soon')
            ->html($emailBody);

        try {
            $this->mailer->send($emailMessage);
        } catch (\Exception $e) {
            $this->logger->error("Failed to send email to {$email}: " . $e->getMessage());
        }
    }

    public function sendResetPasswordEmail(string $email, string $resetToken): void
    {
        $resetUrl = sprintf('%s/reset-password/reset/%s', $this->apiHost, $resetToken);

        $emailBody = $this->twig->render('reset_password/email.html.twig', [
            'resetUrl' => $resetUrl,
        ]);

        $emailMessage = (new Email())
            ->from('noreply@yourdomain.com')
            ->to($email)
            ->subject('Password Reset Request')
            ->html($emailBody);

        try {
            $this->mailer->send($emailMessage);
        } catch (\Exception $e) {
            $this->logger->error("Failed to send password reset email to {$email}: " . $e->getMessage());
            throw new \RuntimeException("Failed to send email: " . $e->getMessage());
        }
    }
}

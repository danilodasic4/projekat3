<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendExpiringRegistrationEmail(string $email, array $cars): void
    {
        try {
            // Render the email body using Twig template
            $emailBody = $this->twig->render('emails/registration_expiring.html.twig', [
                'cars' => $cars,
            ]);

            // Create the email message
            $emailMessage = (new Email())
                ->from('noreply@yourdomain.com')
                ->to($email)
                ->subject('Car Registration Expiring Soon')
                ->html($emailBody);

            // Send the email
            $this->mailer->send($emailMessage);
        } catch (\Exception $e) {
            // Log or handle the error
            throw new \RuntimeException("Failed to send email to {$email}: " . $e->getMessage());
        }
    }
}
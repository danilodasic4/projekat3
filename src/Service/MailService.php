<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendExpiringRegistrationEmail(string $email, array $cars): void
    {
        // Inside HTML email
        $emailBody = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                h1 { color: #2c3e50; }
                p { font-size: 16px; color: #34495e; }
                .car-list { margin-top: 20px; }
                .car-list ul { list-style-type: none; padding: 0; }
                .car-item { margin-bottom: 10px; background-color: #ecf0f1; padding: 10px; border-radius: 5px; }
                .car-item span { font-weight: bold; color: #e74c3c; }
            </style>
        </head>
        <body>
            <h1>Important: Your Car's Registration is Expiring Soon!</h1>
            <p>Dear User,</p>
            <p>We wanted to remind you that the following cars under your ownership have registrations expiring soon:</p>
            <div class='car-list'>
                <ul>";

        // Adding every car in the list
        foreach ($cars as $car) {
            $emailBody .= "
            <li class='car-item'>
                <p><span>Car:</span> " . $car->getBrand() . " " . $car->getModel() . "</p>
                <p><span>Registration Expiry Date:</span> " . $car->getRegistrationDate()->format('Y-m-d') . "</p>
            </li>";
        }

        $emailBody .= "
                </ul>
            </div>
            <p>Please make sure to renew your car's registration before the expiration date to avoid any inconvenience.</p>
            <p>If you have any questions or need assistance, feel free to contact us.</p>
                        <p>If you want to get all information or to book an appointment, please reach us at: <strong>+381601335222</strong></p>
            <p>Best regards,<br>AutoDiler</p>
        </body>
        </html>";

        // Making email
        $emailMessage = (new Email())
            ->from('noreply@yourdomain.com')
            ->to($email)
            ->subject('Car Registration Expiring Soon')
            ->html($emailBody);  // Send HTML email

        // Sending email
         try {
            $this->mailer->send($emailMessage);
        //    dump("Email successfully sent to {$email}");
        } catch (\Exception $e) {
             dump("Failed to send email to {$email}: " . $e->getMessage());
        }
    }
}

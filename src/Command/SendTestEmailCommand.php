<?php
namespace App\Command;

use App\Service\MailService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendTestEmailCommand extends Command
{
    private MailService $mailService;

    public function __construct(MailService $mailService)
    {
        parent::__construct();
        $this->mailService = $mailService;
    }

    protected function configure(): void
    {
        $this->setName('app:send-test-email')
            ->setDescription('Sends a test email');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Sending test email...');
        
        // Send email to a test address
        $this->mailService->sendResetPasswordEmail('test@example.com', 'sample-reset-token');

        $output->writeln('Test email sent successfully!');

        return Command::SUCCESS;
    }
}

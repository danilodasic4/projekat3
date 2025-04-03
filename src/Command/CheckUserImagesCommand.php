<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CheckUserImagesCommand extends Command
{
    protected static $defaultName = 'app:check-user-images';
    protected static $defaultDescription = 'Checks if user images exist on disk';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly LoggerInterface $logger,
        private readonly string $profilePicturesDirectory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::REQUIRED, 'Path to the directory containing user images');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = rtrim($input->getArgument('path'), '/') . '/';
        $users = $this->userRepository->findAll();

        if (empty($users)) {
            $output->writeln('<info>No users found in the database.</info>');
            return Command::SUCCESS;
        }

        $processes = [];

        // Start processes together
        foreach ($users as $user) {
            // Skip users who don't have a profile picture
            if (!$user->getProfilePicture()) {
                $output->writeln(sprintf('User ID %d: No profile picture set', $user->getId()));
                continue;
            }

            $process = new Process(['test', '-f', $path . $user->getProfilePicture()]);
            $process->start();

            $processes[] = [
                'process' => $process,
                'user' => $user,
                'imagePath' => $path . $user->getProfilePicture()
            ];
        }

        // Wait for all processes to finish
        foreach ($processes as $processData) {
            $processData['process']->wait();

            if ($processData['process']->isSuccessful()) {
                $output->writeln(sprintf('User ID %d: Image exists', $processData['user']->getId()));
            } else {
                $output->writeln(sprintf('User ID %d: Image does not exist', $processData['user']->getId()));
                $this->logger->warning('Image not found', [
                    'user' => $processData['user']->getEmail(),
                    'image' => $processData['imagePath']
                ]);
            }
        }

        return Command::SUCCESS;
    }
}

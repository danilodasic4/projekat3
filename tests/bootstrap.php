<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

// Set the environment to 'test' for testing purposes
$_SERVER['APP_ENV'] = 'test';

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Clear the cache for the test environment
$process = new Process(['php', 'bin/console', 'cache:clear', '--env=test']);
$process->run();

if (!$process->isSuccessful()) {
    throw new ProcessFailedException($process);
}

$cacheDir = __DIR__.'/../var/cache/test';

if (is_dir($cacheDir)) {
    $fs = new Filesystem();
    try {
        $fs->remove($cacheDir);
    } catch (IOException $e) {
        echo "Failed to remove test cache directory: " . $e->getMessage() . PHP_EOL;
    }
}

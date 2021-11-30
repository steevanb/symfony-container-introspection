<?php

declare(strict_types=1);

use Steevanb\ParallelProcess\{
    Console\Application\ParallelProcessesApplication,
    Process\Process
};
use Symfony\Component\Console\Input\ArgvInput;

require '/app/vendor/autoload.php';
$rootDir = dirname(__DIR__, 2);

$application = new ParallelProcessesApplication();

$symfonyDir = $rootDir . '/var/symfony';

function addComposerProcess(
    string $rootDir,
    string $symfonyDir,
    ParallelProcessesApplication $application
): Process {
    $process = new Process(
        [
            $rootDir . '/bin/composer',
            '--php=',
            '--no-cache',
            'create-project',
            'symfony/skeleton:6.0.*',
            $symfonyDir
        ]
    );
    $process->setName('composer create-project symfony/skeleton:6.0.*');
    $application->addProcess($process);

    return $process;
}

function addDebugPackProcess(
    string $rootDir,
    string $symfonyDir,
    Process $composerProcess,
    ParallelProcessesApplication $application
): Process {
    $process = (
        new Process(
            [$rootDir . '/bin/composer', '--php=', '--no-cache', 'require', '--dev', 'symfony/debug-pack', '^1.0'],
            $symfonyDir
        )
    )
        ->setName('composer require --dev symfony/debug-pack ^1.0');
    $process->getStartCondition()->addProcessSuccessful($composerProcess);
    $application->addProcess($process);

    return $process;
}

if (is_dir($symfonyDir) === false) {
    $composerProcess = addComposerProcess($rootDir, $symfonyDir, $application);
    $debugPackProcess = addDebugPackProcess($rootDir, $symfonyDir, $composerProcess, $application);
}

$dockerStopProcess = new Process(['bin/symfony/docker-stop', $rootDir]);
$application->addProcess($dockerStopProcess);

$dockerProcess = new Process(['bin/symfony/docker', $rootDir]);
$dockerProcess->getStartCondition()->addProcessSuccessful($dockerStopProcess);
$application->addProcess($dockerProcess);

$application->run(new ArgvInput($argv));

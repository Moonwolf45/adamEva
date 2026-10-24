<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'queues:run',
    description: 'Consume configured Messenger queues in one run'
)]
class QueuesRunCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption(
                'transports',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Transports to consume. You can pass several options or comma-separated values.',
                []
            )
            ->addOption(
                'limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Maximum number of messages to consume.'
            )
            ->addOption(
                'time-limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Maximum seconds to run.'
            );
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $transports = [];

        foreach ($input->getOption('transports') as $value) {
            $transports = array_merge($transports, explode(',', (string) $value));
        }

        if ($transports === []) {
            $envTransports = (string) (
            getenv('MESSENGER_CRON_TRANSPORTS')
                ?: ($_SERVER['MESSENGER_CRON_TRANSPORTS'] ?? '')
            );

            $transports = $envTransports !== ''
                ? explode(',', $envTransports)
                : ['async'];
        }

        $transports = array_map(
            static fn($transport): string => trim((string)$transport),
            $transports
        )
            |> array_filter(...)
            |> array_unique(...)
            |> array_values(...);

        if ($transports === []) {
            $output->writeln('<error>No Messenger transports specified.</error>');

            return Command::FAILURE;
        }

        $application = $this->getApplication();

        if ($application === null) {
            $output->writeln('<error>Console application is not available.</error>');

            return Command::FAILURE;
        }

        $consumeCommand = $application->find('messenger:consume');

        $limit = (int) ($input->getOption('limit') ?: (
            getenv('MESSENGER_CRON_LIMIT')
                ?: ($_SERVER['MESSENGER_CRON_LIMIT'] ?? '100')
            )
        );

        $timeLimit = (int) ($input->getOption('time-limit') ?: (
            getenv('MESSENGER_CRON_TIME_LIMIT')
                ?: ($_SERVER['MESSENGER_CRON_TIME_LIMIT'] ?? '55')
            )
        );

        $output->writeln('<info>Consuming transports: ' . implode(', ', $transports) . '</info>');

        $consumeInput = new ArrayInput([
            'transports' => $transports,
            '--limit' => $limit,
            '--time-limit' => $timeLimit,
            '--allow-no-messages' => true,
            '--no-interaction' => true,
        ]);

        $exitCode = $consumeCommand->run($consumeInput, $output);

        return $exitCode === Command::SUCCESS ? Command::SUCCESS : Command::FAILURE;
    }
}

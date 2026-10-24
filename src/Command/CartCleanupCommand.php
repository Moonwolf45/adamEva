<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\CartRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cart:cleanup',
    description: 'Удаляет корзины, не обновлявшиеся дольше заданного числа дней',
)]
class CartCleanupCommand extends Command
{
    private const int DEFAULT_DAYS = 30;

    public function __construct(
        private readonly CartRepository $cartRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'days',
            null,
            InputOption::VALUE_REQUIRED,
            'Неактивность в днях',
            (string) self::DEFAULT_DAYS
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $days = (int) $input->getOption('days');
        if ($days < 1) {
            $io->error('Опция --days должна быть не меньше 1');

            return Command::INVALID;
        }

        $boundary = new \DateTimeImmutable(sprintf('-%d days', $days));
        $deleted = $this->cartRepository->deleteStale($boundary);

        $io->success(sprintf('Удалено устаревших корзин: %d', $deleted));

        return Command::SUCCESS;
    }
}

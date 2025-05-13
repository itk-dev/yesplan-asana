<?php

namespace App\Command;

use App\Asana\AsanaApiClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:asana:check-board-ids',
    description: 'Add a short description for your command',
)]
class AsanaCheckBoardIdsCommand extends Command
{
    public function __construct(
        private readonly AsanaApiClient $asanaApiClient
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $result = $this->asanaApiClient->checkBoardIds();
        foreach ($result as $key => $items) {
            $io->section($key);

            $table = $io->createTable();
            foreach ($items as $index => $item) {
                if (0 === $index) {
                    $table->setHeaders([array_keys($item)]);
                }
                $table->addRow(array_map(static fn ($value) => is_scalar($value) ? $value : json_encode($value), $item));
            }
            $table->render();
        }

        return Command::SUCCESS;
    }
}

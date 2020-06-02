<?php

namespace App\Command;

use App\Yesplan\EventManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class YesplanDeleteOldEventsCommand extends Command
{
    protected static $defaultName = 'app:yesplan:delete-old-events';

    private $eventManager;

    public function __construct(EventManager $eventManager)
    {
        parent::__construct();
        $this->eventManager = $eventManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->eventManager->deleteOldEvents();

        return 0;
    }
}
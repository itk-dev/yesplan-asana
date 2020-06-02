<?php

/*
 * This file is part of itk-dev/yesplan-asana.
 *
 * (c) 2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command;

use App\Yesplan\EventManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class YesplanGetEventsCommand extends Command
{
    protected static $defaultName = 'app:yesplan:get-events';

    private $eventManager;

    public function __construct(EventManager $eventManager)
    {
        parent::__construct();
        $this->eventManager = $eventManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Get events from Yesplan')

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->eventManager->updateEvents();

        return 0;
    }
}

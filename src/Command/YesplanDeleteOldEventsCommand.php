<?php

namespace App\Command;

use App\Yesplan\EventManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
            ->setDescription('Delete events with eventdate before today from local database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->eventManager->deleteOldEvents();

        return 0;
    }
}

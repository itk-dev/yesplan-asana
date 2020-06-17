<?php

/*
 * This file is part of itk-dev/yesplan-asana.
 *
 * (c) 2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command;

use App\Asana\AsanaEventManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AsanaPutCardsCommand extends Command
{
    protected static $defaultName = 'app:asana:createCards';

    private $asanaEventManager;

    public function __construct(AsanaEventManager $asanaEventManager)
    {
        parent::__construct();
        $this->asanaEventManager = $asanaEventManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Create cards in Asana from YesplanEvent table - creates only new cards')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->asanaEventManager->createCards();
        
        return 0;
    }
}

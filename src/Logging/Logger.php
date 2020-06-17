<?php

/*
 * This file is part of itk-dev/yesplan-asana.
 *
 * (c) 2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use Psr\Log\LoggerTrait;

class Logger
{
    private $logger;

    public function __construct(LoggerTrait $logger)
    {
        $this->logger = $logger;
    }

    public function logInfo(string $message): void
    {
        $this->logger->info($message);
    }

    public function logDebug(string $message): void
    {
        $this->logger->debug($message);
    }

    public function logError(string $message): void
    {
        $this->logger->error($message);
    }
}

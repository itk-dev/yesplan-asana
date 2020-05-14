<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class Logger
{
    private $logger;

    public function __construct(LoggerInterface $logger)
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

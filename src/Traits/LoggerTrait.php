<?php

/*
 * This file is part of itk-dev/yesplan-asana.
 *
 * (c) 2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

 namespace App\Traits;

 use Psr\Log\LoggerAwareTrait;
 use Psr\Log\LoggerTrait as BaseLoggerTrait;

 trait LoggerTrait
 {
   use BaseLoggerTrait;
   use LoggerAwareTrait;

   public function log($level, string|\Stringable $message, array $context = []): void
   {
       if (null !== $this->logger) {
           $this->logger->log($level, $message, $context);
       }
   }

 }

<?php

/*
 * This file is part of itk-dev/yesplan-asana.
 *
 * (c) 2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Yesplan;

use App\Traits\LoggerTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;
class ApiClient
{
 use LoggerTrait;
 private $options;
 private $mailer;
 private $httpClient;

 public function __construct(array $yesplanApiClientOptions, LoggerInterface $logger, MailerController $mailer)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($yesplanApiClientOptions);
        $this->setLogger($logger);
        $this->mailer = $mailer;
    }

   /**
     * Resolve env variables.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'apikey',
            'url',
            'status_id',
            'location_ids',
        ]);

        $resolver->setNormalizer('location_ids', function (Options $options, $value) {
            $value = explode(',', $value);

            return $value;
        });
    }

    public function get(string $path, array $options): ResponseInterface
    {
        $this->debug(sprintf('GET\'ing %s (%s)', $path, json_encode($options)));

        return $this->request('GET', $path, $options);
    }
}

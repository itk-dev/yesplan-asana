<?php

/*
 * This file is part of itk-dev/yesplan-asana.
 *
 * (c) 2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Contracts\HttpClient;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AsanaMockResponse implements ResponseInterface
{
    private $path;
    private $options;

    public function __construct(string $path, array $options)
    {
        $this->path = $path;
        $this->options = $options;
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_CREATED;
    }

    public function getHeaders(bool $throw = true): array
    {
        return [];
    }

    public function getContent(bool $throw = true): string
    {
        return 'dry-run'.\PHP_EOL.\PHP_EOL.json_encode(['path' => $this->path, 'options' => $this->options], \JSON_PRETTY_PRINT);
    }

    public function toArray(bool $throw = true): array
    {
        return [];
    }

    public function cancel(): void
    {
    }

    public function getInfo(?string $type = null): mixed
    {
        return null;
    }
}

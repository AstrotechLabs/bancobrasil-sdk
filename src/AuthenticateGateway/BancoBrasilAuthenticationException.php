<?php

namespace Astrotech\BancoBrasilPix\AuthenticateGateway;

use Exception;

final class BancoBrasilAuthenticationException extends Exception
{
    private string $key;
    private string $description;
    private ?array $responsePayload;

    public function __construct(string $key, string $description, ?array $responsePayload)
    {
        $this->key = $key;
        $this->description = $description;
        $this->responsePayload = $responsePayload;
        parent::__construct("[authentication error: $key] - {$description}");
    }

    public function getResponsePayload(): ?array
    {
        return $this->responsePayload;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}

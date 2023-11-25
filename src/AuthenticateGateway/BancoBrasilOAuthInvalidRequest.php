<?php

namespace Astrotech\BancoBrasilPix\AuthenticateGateway;

use Exception;

final class BancoBrasilOAuthInvalidRequest extends Exception
{
    private string $key;
    private string $description;
    private ?array $responsePayload;

    public function __construct(int $code, string $key, string $description, ?array $responsePayload)
    {
        $this->code = $code;
        $this->key = $key;
        $this->description = $description;
        $this->responsePayload = $responsePayload;
        parent::__construct("[error: $key] - {$description}");
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

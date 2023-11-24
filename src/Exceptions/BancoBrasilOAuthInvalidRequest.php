<?php

namespace Astrotech\Shared\Infra\BancoBrasilPixSdk\Exceptions;

use Exception;

final class BancoBrasilOAuthInvalidRequest extends Exception
{
    private ?array $responsePayload;

    public function __construct(string $key, string $description, ?array $responsePayload)
    {
        $this->responsePayload = $responsePayload;
        parent::__construct("[error: $key] - {$description}");
    }

    public function getResponsePayload(): ?array
    {
        return $this->responsePayload;
    }
}

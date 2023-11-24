<?php

namespace Astrotech\Shared\Infra\BancoBrasilPixSdk\Exceptions;

use Exception;

final class BancoBrasilPixInvalidRequest extends Exception
{
    public function __construct(string $key, string $description)
    {
        parent::__construct("[error: $key] - {$description}");
    }
}

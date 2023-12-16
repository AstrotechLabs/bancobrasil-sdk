<?php

declare(strict_types=1);

namespace AstrotechLabs\BancoBrasilPix\AuthenticateGateway;

final class AuthenticationOutput
{
    public function __construct(
        public readonly string $accessToken
    ) {
    }
}

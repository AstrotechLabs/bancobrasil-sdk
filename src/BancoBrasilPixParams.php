<?php

namespace AstrotechLabs\BancoBrasilPix;

final class BancoBrasilPixParams
{
    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $devAppId,
        public readonly bool $isSandBox = false
    ) {
    }
}

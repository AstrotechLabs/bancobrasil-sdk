<?php

namespace Astrotech\BancoBrasilPix;

final class BancoBrasilPixParams
{
    public function __construct(
        public readonly string $pixKey,
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $devAppId,
        public readonly bool $isSandBox = true,
        public readonly string $pixDescription = '',
    ) {
    }
}

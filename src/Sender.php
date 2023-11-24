<?php

namespace Astrotech\Shared\Infra\BancoBrasilPixSdk;

final class Sender
{
    public function __construct(
        public readonly string $name,
        public readonly string $cpf
    ) {
    }
}

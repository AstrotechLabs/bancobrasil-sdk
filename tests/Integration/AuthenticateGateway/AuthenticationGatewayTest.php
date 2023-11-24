<?php

declare(strict_types=1);

namespace Tests\Integration\AuthenticateGateway;

use Astrotech\BancoBrasilPix\AuthenticateGateway\AuthenticationGateway;
use Astrotech\BancoBrasilPix\AuthenticateGateway\BancoBrasilAuthenticationException;
use Tests\TestCase;

final class AuthenticationGatewayTest extends TestCase
{
    public function testItShouldThrowUnidentifiedCustomerExceptionWhenInvalidCredentialsIsProvided()
    {
        $this->expectException(BancoBrasilAuthenticationException::class);
        $this->expectExceptionMessage('[authentication error: invalid_request] - Software cliente não identificado');

        $sut = new AuthenticationGateway(
            self::$faker->uuid(),
            self::$faker->uuid(),
            boolval($_ENV['BANCO_BRASIL_SANDBOX'])
        );

        $sut->authenticate();
    }

    public function testItShouldReturnAccessTokenWhenValidDataIsProvided()
    {
        $sut = new AuthenticationGateway(
            $_ENV['BANCO_BRASIL_CLIENT_ID'],
            $_ENV['BANCO_BRASIL_CLIENT_SECRET'],
            boolval($_ENV['BANCO_BRASIL_SANDBOX'])
        );

        $result = $sut->authenticate();

        $this->assertNotEmpty($result->accessToken);
    }
}

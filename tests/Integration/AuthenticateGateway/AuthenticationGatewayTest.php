<?php

declare(strict_types=1);

namespace Tests\Integration\AuthenticateGateway;

use AstrotechLabs\BancoBrasilPix\AuthenticateGateway\AuthenticationGateway;
use AstrotechLabs\BancoBrasilPix\AuthenticateGateway\BancoBrasilAuthenticationException;
use AstrotechLabs\BancoBrasilPix\AuthenticateGateway\BancoBrasilOAuthInvalidRequest;
use ReflectionClass;
use Tests\TestCase;
use Tests\Trait\HttpClientMock;

final class AuthenticationGatewayTest extends TestCase
{
    use HttpClientMock;

    public function testItShouldThrowUnidentifiedCustomerExceptionWhenInvalidCredentialsIsProvided()
    {
        $this->expectException(BancoBrasilAuthenticationException::class);
        $this->expectExceptionCode(1001);

        $sut = new AuthenticationGateway(
            self::$faker->uuid(),
            self::$faker->uuid(),
            boolval($_ENV['BANCO_BRASIL_SANDBOX'])
        );

        $reflection = new ReflectionClass($sut);
        $property = $reflection->getProperty('httpClient');
        $property->setValue(
            $sut,
            $this->getHttpClientMockWithException('post', 'invalid-client-id-or-secret.json')
        );

        $sut->authenticate();
    }

    public function testItShouldThrowAnErrorWhenResponseReturnsAnyError()
    {
        $this->expectException(BancoBrasilOAuthInvalidRequest::class);
        $this->expectExceptionCode(1001);

        $sut = new AuthenticationGateway(
            self::$faker->uuid(),
            self::$faker->uuid()
        );

        $reflection = new ReflectionClass($sut);
        $property = $reflection->getProperty('httpClient');
        $property->setValue(
            $sut,
            $this->getHttpClientMock('post', 'invalid-client-id-or-secret.json')
        );

        $sut->authenticate();
    }

    public function testItShouldThrowAnErrorWhenResponseDoesNotReturnAccessTokenKey()
    {
        $this->expectException(BancoBrasilOAuthInvalidRequest::class);
        $this->expectExceptionCode(1002);

        $sut = new AuthenticationGateway(
            self::$faker->uuid(),
            self::$faker->uuid(),
            boolval($_ENV['BANCO_BRASIL_SANDBOX'])
        );

        $reflection = new ReflectionClass($sut);
        $property = $reflection->getProperty('httpClient');
        $property->setValue(
            $sut,
            $this->getHttpClientMock('post', 'authentication-payload-without-token.json')
        );

        $sut->authenticate();
    }

    public function testItShouldReturnAccessTokenWhenValidDataIsProvided()
    {
        $sut = new AuthenticationGateway(
            self::$faker->uuid(),
            self::$faker->uuid(),
            boolval($_ENV['BANCO_BRASIL_SANDBOX'])
        );

        $reflection = new ReflectionClass($sut);
        $property = $reflection->getProperty('httpClient');
        $property->setValue(
            $sut,
            $this->getHttpClientMock('post', 'valid-authentication-payload.json')
        );

        $result = $sut->authenticate();

        $this->assertNotEmpty($result->accessToken);
    }
}

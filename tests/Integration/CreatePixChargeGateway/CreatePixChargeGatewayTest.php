<?php

declare(strict_types=1);

namespace Tests\Integration\CreatePixChargeGateway;

use AstrotechLabs\BancoBrasilPix\AuthenticateGateway\AuthenticationGateway;
use AstrotechLabs\BancoBrasilPix\AuthenticateGateway\AuthenticationOutput;
use AstrotechLabs\BancoBrasilPix\CreatePixChargeGateway\CreatePixChargeException;
use AstrotechLabs\BancoBrasilPix\CreatePixChargeGateway\CreatePixChargeGateway;
use AstrotechLabs\BancoBrasilPix\CreatePixChargeGateway\PixData;
use ReflectionClass;
use Tests\TestCase;
use Tests\Trait\HttpClientMock;

final class CreatePixChargeGatewayTest extends TestCase
{
    use HttpClientMock;

    private function getAccessTokenMocked(): string
    {
        $authenticationGatewayMock = $this->createMock(AuthenticationGateway::class);

        $authenticationGatewayMock
            ->method('authenticate')
            ->willReturn(new AuthenticationOutput(self::$faker->uuid()));

        return $authenticationGatewayMock->authenticate()->accessToken;
    }

    public function testItShouldThrowAnExceptionWhenBbApiIsDown()
    {
        $this->expectException(CreatePixChargeException::class);
        $this->expectExceptionCode(1001);

        $createPixChargeGateway = new CreatePixChargeGateway(
            $this->getAccessTokenMocked(),
            self::$faker->uuid(),
            boolval($_ENV['BANCO_BRASIL_SANDBOX'])
        );

        $reflection = new ReflectionClass($createPixChargeGateway);
        $property = $reflection->getProperty('httpClient');
        $property->setValue(
            $createPixChargeGateway,
            $this->getHttpClientMockWithException('put', 'system-down-payload.json')
        );

        $createPixChargeGateway->createCharge(new PixData(
            self::$faker->name(),
            '01234567890',
            10,
            '50650051000137'
        ));
    }

    public function testItShouldThrowAnExceptionWhenInvalidFormatRequestPayloadIsProvided()
    {
        $this->expectException(CreatePixChargeException::class);
        $this->expectExceptionCode(1001);

        $createPixChargeGateway = new CreatePixChargeGateway(
            $this->getAccessTokenMocked(),
            self::$faker->uuid(),
            boolval($_ENV['BANCO_BRASIL_SANDBOX'])
        );

        $reflection = new ReflectionClass($createPixChargeGateway);
        $property = $reflection->getProperty('httpClient');
        $property->setValue(
            $createPixChargeGateway,
            $this->getHttpClientMockWithException('put', 'charge-invalid-request-format.json')
        );

        $createPixChargeGateway->createCharge(new PixData(
            self::$faker->name(),
            '01234567890',
            10,
            '50650051000137'
        ));
    }

    public function testItShouldCreateAPixChargeWhenCorrectDataIsProvided()
    {
        $createPixChargeGateway = new CreatePixChargeGateway(
            $this->getAccessTokenMocked(),
            $_ENV['BANCO_BRASIL_DEV_APP_ID']
        );

        $reflection = new ReflectionClass($createPixChargeGateway);
        $property = $reflection->getProperty('httpClient');
        $property->setValue(
            $createPixChargeGateway,
            $this->getHttpClientMock('put', 'valid-charge-payload.json')
        );

        $result = $createPixChargeGateway->createCharge(new PixData(
            self::$faker->name(),
            '01234567890',
            10,
            '50650051000137'
        ));

        $this->assertNotEmpty($result->txId);
        $this->assertNotEmpty($result->copyPasteKey);
    }
}

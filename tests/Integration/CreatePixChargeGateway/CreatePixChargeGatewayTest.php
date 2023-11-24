<?php

declare(strict_types=1);

namespace Tests\Integration\CreatePixChargeGateway;

use Astrotech\BancoBrasilPix\AuthenticateGateway\AuthenticationGateway;
use Astrotech\BancoBrasilPix\CreatePixChargeGateway\CreatePixChargeGateway;
use Astrotech\BancoBrasilPix\CreatePixChargeGateway\PixData;
use Tests\TestCase;

final class CreatePixChargeGatewayTest extends TestCase
{
    public function testItShouldCreateAPixChargeWhenCorrectDataIsProvided()
    {
        $sut = new AuthenticationGateway(
            $_ENV['BANCO_BRASIL_CLIENT_ID'],
            $_ENV['BANCO_BRASIL_CLIENT_SECRET'],
            boolval($_ENV['BANCO_BRASIL_SANDBOX'])
        );

        $result = $sut->authenticate();

        $createPixChargeGateway = new CreatePixChargeGateway(
            $result->accessToken,
            $_ENV['BANCO_BRASIL_DEV_APP_ID'],
            boolval($_ENV['BANCO_BRASIL_SANDBOX'])
        );

        /** https://apoio.developers.bb.com.br/referency/post/648384cadcefbe00128886e1 */
        $result = $createPixChargeGateway->createCharge(new PixData(
            self::$faker->name(),
            '01234567890',
            10,
            '50650051000137'
        ));

        $this->assertNotEmpty($result->txId);
        $this->assertNotEmpty($result->qrCode);
    }
}

<?php

namespace Astrotech\BancoBrasilPix;

use Astrotech\BancoBrasilPix\AuthenticateGateway\AuthenticationGateway;
use Astrotech\BancoBrasilPix\CreatePixChargeGateway\CreatePixChargeGateway;
use Astrotech\BancoBrasilPix\CreatePixChargeGateway\PixData;

final class BancoBrasilPix
{
    private readonly AuthenticationGateway $authenticationGateway;

    public function __construct(
        private readonly BancoBrasilPixParams $params
    ) {
        $this->authenticationGateway = new AuthenticationGateway(
            $params->clientId,
            $this->params->clientSecret,
            $this->params->isSandBox
        );
    }

    public function createCharge(PixData $pixData): array
    {
        $authData = $this->authenticationGateway->authenticate();

        $createPixChargeGateway = new CreatePixChargeGateway(
            $authData->accessToken,
            $this->params->devAppId,
            $this->params->isSandBox
        );

        return $createPixChargeGateway->createCharge($pixData)->toArray();
    }
}

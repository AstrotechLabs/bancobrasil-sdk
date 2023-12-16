<?php

namespace AstrotechLabs\BancoBrasilPix;

use AstrotechLabs\BancoBrasilPix\AuthenticateGateway\AuthenticationGateway;
use AstrotechLabs\BancoBrasilPix\CreatePixChargeGateway\CreatePixChargeGateway;
use AstrotechLabs\BancoBrasilPix\CreatePixChargeGateway\PixData;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

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

        $createCharge = $createPixChargeGateway->createCharge($pixData);
        $options = new QROptions(['version' => QRCode::VERSION_AUTO, 'imageTransparent' => false]);

        return [
            ...$createCharge->toArray(),
            'qrCode' => (new QRCode($options))->render($createCharge->copyPasteKey)
        ];
    }
}

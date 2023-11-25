<?php

declare(strict_types=1);

namespace Astrotech\BancoBrasilPix\CreatePixChargeGateway;

use Astrotech\BancoBrasilPix\Exceptions\BancoBrasilPixInvalidRequest;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

final class CreatePixChargeGateway
{
    private GuzzleClient $httpClient;

    public function __construct(
        private readonly string $accessToken,
        private readonly string $devAppId,
        private readonly bool $isSandBox = false,
    ) {
        $baseUrl = $this->isSandBox
            ? 'https://api.hm.bb.com.br'
            : 'https://api-pix.bb.com.br';

        $this->httpClient = new GuzzleClient([
            'base_uri' => $baseUrl,
            'timeout' => 10
        ]);
    }

    public function createCharge(PixData $pixData): CreatePixChargeOutput
    {
        $headers = [
            "Content-Type" => "application/json",
            "Authorization" => "Bearer {$this->accessToken}",
            "X-Application-Key" => $this->devAppId
        ];

        $body = [
            'calendario' => [
                'expiracao' => (string)$pixData->expiration
            ],
            'devedor' => [
                'cpf' => $pixData->senderCpf,
                'nome' => $pixData->senderName
            ],
            'valor' => [
                'original' => (string)$pixData->amount
            ],
            'chave' => $pixData->destinationKey,
            'solcnpjitacaoPagador' => $pixData->description,
        ];

        $txId = md5(substr(md5((string)mt_rand()), 0, 7));

        try {
            $response = $this->httpClient->put("/pix/v2/cob/{$txId}", [
                'headers' => $headers,
                'json' => $body
            ]);
        } catch (ClientException $e) {
            $responsePayload = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new CreatePixChargeException(
                1001,
                $responsePayload['detail'],
                $responsePayload['type'],
                $body,
                $responsePayload
            );
        }

        $responsePayload = json_decode($response->getBody()->getContents(), true);

        return new CreatePixChargeOutput($txId, $responsePayload['location']);
    }
}

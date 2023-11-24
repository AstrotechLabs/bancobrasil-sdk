<?php

declare(strict_types=1);

namespace Astrotech\BancoBrasilPix\CreatePixChargeGateway;

use Astrotech\BancoBrasilPix\Exceptions\BancoBrasilPixInvalidRequest;
use GuzzleHttp\Client as GuzzleClient;

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

        $response = $this->httpClient->put("/pix/v2/cob/{$txId}", [
            'headers' => $headers,
            'json' => $body
        ]);

        $responsePayload = json_decode($response->getBody()->getContents(), true);

        if (isset($responsePayload['error'])) {
            throw new BancoBrasilPixInvalidRequest($responsePayload['error'], $responsePayload['message']);
        }

        if (isset($responsePayload['erros'])) {
            throw new BancoBrasilPixInvalidRequest(
                $responsePayload['erros'][0]['codigo'],
                $responsePayload['erros'][0]['mensagem']
            );
        }

        return new CreatePixChargeOutput($txId, $responsePayload['textoImagemQRcode']);
    }
}

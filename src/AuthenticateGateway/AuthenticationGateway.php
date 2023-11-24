<?php

declare(strict_types=1);

namespace Astrotech\BancoBrasilPix\AuthenticateGateway;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

final class AuthenticationGateway
{
    private GuzzleClient $httpClient;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly bool $isSandBox = false,
    ) {
        $baseUrl = $this->isSandBox
            ? 'https://oauth.hm.bb.com.br'
            : 'https://oauth.bb.com.br';

        $this->httpClient = new GuzzleClient([
            'base_uri' => $baseUrl,
            'timeout' => 10
        ]);
    }

    public function authenticate(): AuthenticationOutput
    {
        $body = [
            'grant_type' => 'client_credentials',
            'scope' => 'cob.read cob.write pix.read pix.write'
        ];

        $headers = [
            "Authorization" => "Basic " . base64_encode("{$this->clientId}:{$this->clientSecret}"),
            "Content-Type" => "application/x-www-form-urlencoded"
        ];

        try {
            $response = $this->httpClient->post('/oauth/token', [
                'headers' => $headers,
                'form_params' => $body
            ]);
        } catch (ClientException $e) {
            $responsePayload = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new BancoBrasilAuthenticationException(
                $responsePayload['error'],
                $responsePayload['error_description'],
                $responsePayload
            );
        }

        $responsePayload = json_decode($response->getBody()->getContents(), true);

        if (isset($responsePayload['error'])) {
            throw new BancoBrasilOAuthInvalidRequest(
                $responsePayload['error'],
                $responsePayload['error_description'],
                $responsePayload
            );
        }

        if (!isset($responsePayload['access_token'])) {
            throw new BancoBrasilOAuthInvalidRequest(
                '0000001',
                'Token de Autenticação BB não foi informado',
                $responsePayload
            );
        }

        return new AuthenticationOutput($responsePayload['access_token']);
    }
}

<?php

declare(strict_types=1);

namespace AstrotechLabs\BancoBrasilPix\AuthenticateGateway;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

class AuthenticationGateway
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
                1001,
                $responsePayload['error'],
                $responsePayload['error_description'],
                $responsePayload
            );
        }

        $responsePayload = json_decode($response->getBody()->getContents(), true);

        if (isset($responsePayload['error'])) {
            throw new BancoBrasilOAuthInvalidRequest(
                1001,
                $responsePayload['error'],
                $responsePayload['error_description'],
                $responsePayload
            );
        }

        if (!isset($responsePayload['access_token'])) {
            throw new BancoBrasilOAuthInvalidRequest(
                1002,
                'access_token_not_found',
                'Token de Autenticação BB não foi informado',
                $responsePayload
            );
        }

        return new AuthenticationOutput($responsePayload['access_token']);
    }
}

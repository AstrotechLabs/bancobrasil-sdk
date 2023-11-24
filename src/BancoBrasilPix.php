<?php

namespace Astrotech\Shared\Infra\BancoBrasilPixSdk;

use Astrotech\Shared\Infra\BancoBrasilPixSdk\Exceptions\BancoBrasilOAuthInvalidRequest;
use Astrotech\Shared\Infra\BancoBrasilPixSdk\Exceptions\BancoBrasilOAuthRequestException;
use Astrotech\Shared\Infra\BancoBrasilPixSdk\Exceptions\BancoBrasilPixInvalidRequest;

final class BancoBrasilPix
{
    private bool $isSandBox;
    private string $devAppId;
    private string $pixKey;
    private string $oAuthBaseUrl;
    private string $pixBaseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $accessToken;
    private array $authResponse;
    private array $paymentResponse;
    private string $txId;
    private string $pixDescription;
    private string $paymentPixKey;

    public function __construct(BbPixParams $params)
    {
        $this->isSandBox = $params->isSandBox;
        $this->clientId = trim($params->clientId);
        $this->clientSecret = trim($params->clientSecret);
        $this->devAppId = trim($params->devAppId);
        $this->pixKey = trim($params->pixKey);
        $this->pixDescription = trim($params->pixDescription);

        $this->oAuthBaseUrl = $this->isSandBox
            ? 'https://oauth.hm.bb.com.br'
            : 'https://oauth.bb.com.br';

        $this->pixBaseUrl = $this->isSandBox
            ? 'https://api.hm.bb.com.br/pix/v1'
            : 'https://api.bb.com.br/pix/v1';
    }

    public function createCharge(Sender $sender, float $amount)
    {
        $this->authenticate();
        $this->txId = md5(substr(md5(mt_rand()), 0, 7));

        $headers = [
            "Authorization: Bearer {$this->accessToken}",
            "Content-Type: application/json",
            "X-Developer-Application-Key: {$this->devAppId}"
        ];

        $body = json_encode([
            'calendario' => [
                'expiracao' => '36000'
            ],
            'devedor' => [
                'cpf' => $sender->cpf,
                'nome' => $sender->name
            ],
            'valor' => [
                'original' => (string)$amount
            ],
            'chave' => $this->pixKey,
            'solicitacaoPagador' => $this->pixDescription,
        ]);

        $this->paymentResponse = $this->request(
            "{$this->pixBaseUrl}/cobqrcode/{$this->txId}",
            'PUT',
            $body,
            $headers
        );

        if (isset($this->paymentResponse['error'])) {
            throw new BancoBrasilPixInvalidRequest($this->paymentResponse['error'], $this->paymentResponse['message']);
        }

        if (isset($this->paymentResponse['erros'])) {
            throw new BancoBrasilPixInvalidRequest(
                $this->paymentResponse['erros'][0]['codigo'],
                $this->paymentResponse['erros'][0]['mensagem']
            );
        }

        $this->paymentPixKey = $this->paymentResponse['textoImagemQRcode'];
    }

    private function authenticate(): void
    {
        $body = http_build_query([
            'grant_type' => 'client_credentials',
            'scope' => 'cob.read cob.write pix.read pix.write'
        ]);

        $headers = [
            "Authorization: Basic " . base64_encode("{$this->clientId}:{$this->clientSecret}"),
            "Content-Type: application/x-www-form-urlencoded"
        ];

        $response = $this->request("{$this->oAuthBaseUrl}/oauth/token", 'POST', $body, $headers);

        if (isset($response['error'])) {
            throw new BancoBrasilOAuthInvalidRequest($response['error'], $response['error_description'], $response);
        }

        if (!isset($response['access_token'])) {
            throw new BancoBrasilOAuthInvalidRequest(
                '0000001',
                'Token de Autenticação BB não foi encontrado',
                $response
            );
        }

        $this->authResponse = $response;
        $this->accessToken = $response['access_token'];
    }

    private function request(string $url, string $method, string $body, array $headers = []): array
    {
        $headers = array_merge($headers, ['Content-Length: ' . strlen($body)]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        $response = curl_exec($ch);

        if (empty($response)) {
            throw new BancoBrasilOAuthRequestException();
        }

        return json_decode($response, true);
    }

    public function getTxId(): string
    {
        return $this->txId;
    }

    public function getPaymentPixKey(): string
    {
        return $this->paymentPixKey;
    }

    public function getAuthResponse(): array
    {
        return $this->authResponse;
    }

    public function getPaymentResponse(): array
    {
        return $this->paymentResponse;
    }
}

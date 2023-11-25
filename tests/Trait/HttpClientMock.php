<?php

declare(strict_types=1);

namespace Tests\Trait;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request as Request;
use GuzzleHttp\Psr7\Response as Response;

trait HttpClientMock
{
    protected function getHttpClientMockWithException(string $verb, string $mockFile, int $statusCode = 400): GuzzleClient
    {
        $httpClientMock = $this->createMock(GuzzleClient::class);

        $httpClientMock
            ->method($verb)
            ->willThrowException(new ClientException(
                    self::$faker->paragraph(),
                    new Request($verb, self::$faker->url()),
                    new Response(status: $statusCode, body: file_get_contents(dirname(__DIR__) . "/Fakes/{$mockFile}")))
            );

        return $httpClientMock;
    }

    protected function getHttpClientMock(string $verb,  string $mockFile, int $statusCode = 200): GuzzleClient
    {
        $httpClientMock = $this->createMock(GuzzleClient::class);

        $httpClientMock
            ->method($verb)
            ->willReturn(new Response(
                status: $statusCode,
                body: file_get_contents(dirname(__DIR__) . "/Fakes/{$mockFile}"))
            );

        return $httpClientMock;
    }
}

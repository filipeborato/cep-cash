<?php

namespace App\Tests\Controller;

use App\Controller\AddressController;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class AddressControllerTest extends TestCase
{
    private $cacheMock;
    private $clientMock;
    private $loggerMock;
    private $paramsMock;

    protected function setUp(): void
    {
        // Cria mocks das dependências
        $this->cacheMock = $this->createMock(CacheItemPoolInterface::class);
        $this->clientMock = $this->createMock(Client::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->paramsMock = $this->createMock(ParameterBagInterface::class);

        // Configura o mock do parâmetro para retornar a URL do Redis
        $this->paramsMock->method('get')->willReturn('redis://localhost');
    }

    public function testGetAddressSuccess()
    {
        // Configura a resposta esperada
        $expectedData = [
            'cep' => '01001-000',
            'logradouro' => 'Praça da Sé',
            'complemento' => 'lado ímpar',
            'bairro' => 'Sé',
            'localidade' => 'São Paulo',
            'uf' => 'SP',
            'ibge' => '3550308',
            'gia' => '1004',
            'ddd' => '11',
            'siafi' => '7107',
        ];

        // Configura o item do cache
        $cacheItemMock = $this->createMock(CacheItemInterface::class);
        $this->cacheMock->method('getItem')->willReturn($cacheItemMock);
        $cacheItemMock->expects($this->once())->method('isHit')->willReturn(false);

        // Configura a resposta da API ViaCEP
        $response = new Response(200, [], json_encode($expectedData));
        $this->clientMock->method('get')->willReturn($response);

        // Configura o cache para salvar a resposta
        $cacheItemMock->method('set')->with($expectedData);

        // Executa o teste
        $controller = new AddressController($this->paramsMock, $this->loggerMock);
        $response = $controller->getAddress('01001-000');

        // Verifica a resposta
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedData), $response->getContent());
    }

    public function testGetAddressApiError()
    {
        // Configura o item do cache
        $cacheItemMock = $this->createMock(CacheItemInterface::class);
        $this->cacheMock->method('getItem')->willReturn($cacheItemMock);
        $cacheItemMock->expects($this->once())->method('isHit')->willReturn(false);

        // Configura uma resposta de erro da API ViaCEP
        $this->clientMock->method('get')->willThrowException(new \Exception('Error querying ViaCEP'));

        // Logger deve registrar o erro
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Error fetching address'));

        // Executa o teste
        $controller = new AddressController($this->paramsMock, $this->loggerMock);
        $response = $controller->getAddress('01001-000');

        // Verifica a resposta de erro
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'Could not fetch address']), $response->getContent());
    }
}

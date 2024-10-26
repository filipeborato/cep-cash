<?php

namespace App\Controller;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class AddressController
{
    private $cache;
    private $httpClient;

    public function __construct()
    {
        // Conexão com o Redis usando o adaptador de cache do Symfony
        $redisConnection = RedisAdapter::createConnection('redis://redis:6379');
        $this->cache = new RedisAdapter($redisConnection);

        $this->httpClient = new Client(); // Inicializa o cliente Guzzle
    }

    #[Route('/cep/{cep}', name: 'get_address', methods: ['GET'])]
    public function getAddress(string $cep): JsonResponse
    {        
        $cacheKey = 'cep_' . $cep;

        $cachedAddress = $this->cache->get($cacheKey, function (ItemInterface $item) use ($cep) {           
            
            $response = $this->httpClient->get("https://viacep.com.br/ws/{$cep}/json/");
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Erro ao consultar o ViaCEP');
            }
            
            return json_decode($response->getBody()->getContents(), true);
        });

        return new JsonResponse($cachedAddress);
    }
}

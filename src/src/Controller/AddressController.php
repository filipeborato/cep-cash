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
        $redisConnection = RedisAdapter::createConnection($_ENV['REDIS_URL']);
        $this->cache = new RedisAdapter($redisConnection);
        $this->httpClient = new Client(); 
    }

    #[Route('/cep/{cep}', name: 'get_address', methods: ['GET'])]
    public function getAddress(string $cep): JsonResponse
    {        
        $cacheKey = 'cep_' . $cep;
        try {
            $cachedAddress = $this->cache->get($cacheKey, function (ItemInterface $item) use ($cep) {           
                
                $response = $this->httpClient->get("https://viacep.com.br/ws/{$cep}/json/");
                if ($response->getStatusCode() !== 200) {
                    throw new \Exception('Erro ao consultar o ViaCEP');
                }
                
                return json_decode($response->getBody()->getContents(), true);
            });
        }
         catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage(), 'erro' => true], 400);
        }

        return new JsonResponse($cachedAddress);
    }
}

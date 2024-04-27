<?php

namespace Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\SimpleCache\CacheInterface;
use Throwable;

use function json_encode;

class Home extends AbstractController
{
    /**
     * @throws \Slim\Exception\HttpException
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $response->getBody()
                ->write(
                    json_encode(
                        [
                            'code' => 0,
                            'message' => 'OK',
                            'details' => [
                                'cache' => sprintf('%.3f ms', $this->testCache()),
                            ],
                        ],
                        JSON_THROW_ON_ERROR
                    )
                );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (Throwable $t) {
            throw $this->internalErrorException($request, $t->getMessage(), $t);
        }
    }

    private function testCache(): float
    {
        $cache = $this->get(CacheInterface::class);
        $start = microtime(true);
        $cache->ping();

        return round((microtime(true) - $start) * 1000, 3);
    }
}

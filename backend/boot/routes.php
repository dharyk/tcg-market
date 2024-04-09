<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

/** RouteCollectorProxy $api */

$api->get('/', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("Hello beautiful world!");
    return $response;
});

// Catch-all ***MUST BE DEFINED LAST***
$api->options('/{routes:.+}', function ($req, $res, $args) {
    return $res->withStatus(204)
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Authorization, Accept, Content-Type');
});

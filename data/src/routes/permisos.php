<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Ver permisos
$app->get('/permisos', function(Request $request, Response $response) use ($crud)
{
    return $response->withJson(['CRUD' => $crud]);
});
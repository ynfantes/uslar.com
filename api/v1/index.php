<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$config = ["Settings" => [
    "displayErrorDetails" => true
]];

$app = new \Slim\App;

function anError($th, $res) {
    $data = ['status'=>'error', 'message'=>$th->getMessage()];
    $newResponse = $res->withJson($data, 500);
    return $newResponse;
}

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    
    return $response;
});

$app->get('/status', function (Request $request, Response $response) {
    
    try {
        
        $data = ['status'=>'OK'];
        $newResponse = $response->withJson($data);
        return $newResponse;

    } catch (\Throwable $th) {
        //throw $th;
        return anError($th,$response);
    }
    
});

require_once '../src/routes/routes.php';

$app->run();
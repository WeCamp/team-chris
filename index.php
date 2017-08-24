<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['file'] = "db/test.db";

$app = new \Slim\App(["settings" => $config]);

$container = $app->getContainer();

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("sqlite:" . $db['file']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['ratings'] = function ($c) {
    $ratings = new TeamChris\App($c['db']);
    return $ratings;
};

// Register component on container
$container['view'] = function ($container) {
    return new \Slim\Views\PhpRenderer('public');
};

$app->get('/', function ($request, $response, $args) {
    return $this->view->render($response, 'index.html');
})->setName('index');

$app->get('/api/reviews', function (Request $request, Response $response, $args) {
    $params = $request->getQueryParams();
    /**
     * @var TeamChris\App $ratings
     */
    $ratings = $this->ratings;
    if ($params == null) {

    }
    return $response->withStatus(200)
        ->withJson(['message' => 'We are at WeCamp!']);
});

$app->post('/api/reviews', function (Request $request, Response $response, $args) {
    /**
     * @var TeamChris\App $ratings
     */
    $ratings = $this->ratings;

    $data = $request->getParsedBody();
    $answer = $ratings->rateAPlace($data);
    return $response->withStatus(200)
        ->withJson($answer);
});



$app->run();
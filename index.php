<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['file'] = "db/database.db";

$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) {
    return new \Slim\Views\PhpRenderer('public');
};

$app->get('/', function ($request, $response, $args) {
    return $this->view->render($response, 'index.html');
})->setName('index');

$app->get('/api/reviews', function (Request $request, Response $response, $args) {
    $params = $request->getQueryParams();
    $db = new PDO('sqlite:db/database.db');
    $ratings = new TeamChris\App($db);

    if (isset($params['place_ids']) && isset($params['categories'])) {

        array_map(function ($item) {
            return htmlspecialchars($item);
        }, $params['place_ids']);

        array_map(function ($item) {
            return htmlspecialchars($item);
        }, $params['categories']);
        $result = $ratings->checkPlaces(['placeIds' => $params['place_ids'], 'categories' => $params['categories']]);

        return $response->withStatus(200)
            ->withJson($result);
    } else {
        return $response->withStatus(400)
            ->withJson(['message' => 'error']);
    }
});

$app->post('/api/reviews', function (Request $request, Response $response, $args) {

    $db = new PDO('sqlite:db/database.db');
    $ratings = new TeamChris\App($db);

    $data = $request->getParsedBody();

    if (isset($data['placeId']) && isset($data['category']) && isset($data['rating'])) {
        filter_var($data['placeId'], FILTER_SANITIZE_STRING);
        filter_var($data['category'], FILTER_SANITIZE_STRING);
        filter_var($data['rating'], FILTER_SANITIZE_NUMBER_INT);
        $answer = $ratings->rateAPlace($data['placeId'], $data['category'], $data['rating']);
        return $response->withStatus(200)
            ->withJson($answer);
    } else {
        return $response->withStatus(400)
            ->withJson(['message' => 'error']);
    }
});


$app->run();
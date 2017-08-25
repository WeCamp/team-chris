<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;

// Serve up static CSS/JS files
if (preg_match('/\.(css|js)$/', $_SERVER['PHP_SELF'], $matches)) {
    $fileExtension = $matches[1];

    if (!file_exists(__DIR__ . '/public' . $_SERVER['PHP_SELF'])) {
        echo $fileExtension . ' file not found - ' . $_SERVER['PHP_SELF'];
    }

    switch ($fileExtension) {
        case 'js';
            $contentType = 'application/javascript';
            break;

        case 'css';
            $contentType = 'text/css';
            break;

        default:
            throw new \LogicException('Unknown CSS/JS file extension - ' . $fileExtension);
    }

    $content = file_get_contents(__DIR__ . '/public' . $_SERVER['PHP_SELF']);
    header('Content-type: ' . $contentType, true);
    echo $content;
    die;
}

// Serve up static image files
if (preg_match('/\/(img)\/.*\.(.*)$/', $_SERVER['PHP_SELF'], $matches)) {
    $fileExtension = $matches[2];

    if (!file_exists(__DIR__ . '/public' . $_SERVER['PHP_SELF'])) {
        echo $fileExtension . ' file not found - ' . $_SERVER['PHP_SELF'];
    }

    switch ($fileExtension) {
        case 'jpg';
        case 'jpeg';
            $contentType = 'image/jpeg';
            break;

        default:
            throw new \LogicException('Unknown image file extension - ' . $fileExtension);
    }

    $content = file_get_contents(__DIR__ . '/public' . $_SERVER['PHP_SELF']);
    header('Content-type: ' . $contentType, true);
    echo $content;
    die;
}

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



    $missingParams = [];
    foreach (['placeIds', 'categories'] as $key) {
        if (!isset($params[$key])) {
            $missingParams[] = $key;
        }
    }

    if (empty($missingParams)) {

        $result = $ratings->checkPlaces(['placeIds' => $params['placeIds'], 'categories' => $params['categories']]);

        return $response->withStatus(200)
            ->withJson($result);
    } else {
        return $response->withStatus(400)
            ->withJson(['message' => 'Error: Missing required params... ' . implode(', ', $missingParams)]);
    }
});

$app->post('/api/reviews', function (Request $request, Response $response, $args) {

    $db = new PDO('sqlite:db/database.db');
    $ratings = new TeamChris\App($db);

    $body = $request->getParsedBody();

    // @todo Update this to handle all the posted resports, not just the first one
    $data = $body['data'][0];

    $missingFields = [];
    foreach (['placeId', 'category', 'vote'] as $key) {
        if (!isset($data[$key])) {
            $missingFields[] = $key;
        }
    }


    if (empty($missingFields)) {
        [
            'placeId'  => $placeId,
            'category' => $category,
            'vote'     => $vote,
        ] = $data;

        $answer = $ratings->rateAPlace($placeId, $category, $vote);

        return $response->withStatus(200)
            ->withJson($answer);
    } else {
        return $response->withStatus(400)
            ->withJson(['message' => 'Error: Missing require fields... ' . implode(', ', $missingFields)]);
    }
});


$app->run();
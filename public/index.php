<?php

declare(strict_types=1);

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
AppFactory::setContainer($container);

$container->set('view', function () {
    return Twig::create('../templates');
});

$app = AppFactory::create();

$app->add(TwigMiddleware::createFromContainer($app));

$app->get('/', function (Request $request, Response $response) {
    return $this->get('view')->render($response, 'app/home.html.twig');
})->setName('home');

$app->run();

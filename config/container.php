<?php

declare(strict_types=1);

use function DI\autowire;
use App\Repository\DbUrlCheckRepository;
use App\Repository\DbUrlRepository;
use App\Repository\UrlCheckRepositoryInterface;
use App\Repository\UrlRepositoryInterface;
use App\Service\DbConnection;
use Slim\Flash\Messages;
use Slim\Views\Twig;

return [
    Twig::class                        => fn() => Twig::create('../templates'),
    Messages::class                    => fn() => new Messages(),
    PDO::class                         => fn() => DbConnection::get()->connect(),
    UrlRepositoryInterface::class      => autowire(DbUrlRepository::class),
    UrlCheckRepositoryInterface::class => autowire(DbUrlCheckRepository::class),
    'commands'                         => fn() => require __DIR__ . '/commands.php',
    'settings'                         => [
        'displayErrorDetails' => getenv('APP_ENV') === 'development'
    ]
];

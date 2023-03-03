<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UrlNotFoundException;
use App\Repository\UrlCheckRepositoryInterface;
use App\Repository\UrlRepositoryInterface;
use App\Service\UrlChecker;
use DateTimeImmutable;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Slim\Flash\Messages;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class CheckUrlsController
{
    public function __construct(
        private RouteCollectorInterface $routeCollector,
        private Twig $twig,
        private Messages $flash,
        private UrlRepositoryInterface $urlRepository,
        private UrlCheckRepositoryInterface $urlCheckRepository,
        private UrlChecker $urlChecker
    ) {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws GuzzleException
     * @throws LoaderError
     */
    public function __invoke(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        try {
            $urlId = $args['id'];
            $url = $this->urlRepository->getOne($urlId);
            try {
                $checkResult = $this->urlChecker->check($url['name']);

                $check = $this->buildNewCheck($urlId, $checkResult);

                $this->urlCheckRepository->add($check);

                $this->flash->addMessage('success', 'Страница успешно проверена');
            } catch (ConnectException) {
                $this->flash->addMessage('error', 'Произошла ошибка при проверке, не удалось подключиться');
            } catch (RequestException) {
                $this->flash->addMessage('error', 'Произошла ошибка при проверке. Ошибка при выполнении запроса');
            }

            return $response->withRedirect($this->routeCollector->getRouteParser()->urlFor('url', ['id' => $urlId]));
        } catch (UrlNotFoundException) {
            return $this->twig->render($response->withStatus(404), 'app/404.html.twig');
        } catch (Exception $e) {
            $this->flash->addMessageNow('error', $e->getMessage());
            $data = ['flashes' => $this->flash->getMessages()];

            return $this->twig->render($response->withStatus(500), 'app/500.html.twig', $data);
        }
    }

    private function buildNewCheck(string $urlId, array $checkResult): array
    {
        return [
            'url_id'      => $urlId,
            'created_at'  => (new DateTimeImmutable())->format('c'),
            'status_code' => $checkResult['status_code'],
            'h1'          => $checkResult['h1'],
            'title'       => $checkResult['title'],
            'description' => $checkResult['description'],
        ];
    }
}

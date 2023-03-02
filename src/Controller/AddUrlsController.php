<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UrlRepositoryInterface;
use App\Service\UrlNormalizer;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;
use Slim\Flash\Messages;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Valitron\Validator;

class AddUrlsController
{
    public function __construct(
        private Twig $twig,
        private Messages $flash,
        private RouteCollectorInterface $routeCollector,
        private UrlRepositoryInterface $urlRepository,
        private UrlNormalizer $urlNormalizer
    ) {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function __invoke(ServerRequest $request, Response $response): ResponseInterface
    {
        $url = $request->getParsedBodyParam('url');

        $validator = new Validator($url);
        $validator->setPrependLabels(false);
        $validator->rule('required', 'name')->message('URL не должен быть пустым');
        $validator->rule('lengthMax', 'name', 255)->message('URL не должен быть пустым');
        $validator->rule('url', 'name')->message('Некорректный URL');

        if ($validator->validate()) {
            $url['name'] = $this->urlNormalizer->normalize($url['name']);

            if (!$existingUrl = $this->urlRepository->findOneByName($url['name'])) {
                $url['created_at'] = (new DateTimeImmutable())->format('c');

                $id = $this->urlRepository->add($url);

                $this->flash->addMessage('success', 'Страница успешно добавлена');
            } else {
                $id = $existingUrl['id'];

                $this->flash->addMessage('success', 'Страница уже существует');
            }

            return $response->withRedirect(
                $this->routeCollector->getRouteParser()->urlFor('url', ['id' => $id])
            );
        }

        $params = [
            'url'     => $url,
            'errors'  => $validator->errors(),
            'flashes' => $this->flash->getMessages(),
            'errorMessage' => print_r($validator->errors(), true)
        ];

        return $this->twig->render($response->withStatus(422), 'app/home.html.twig', $params);
    }
}

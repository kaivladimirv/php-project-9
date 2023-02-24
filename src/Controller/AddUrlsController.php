<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UrlRepositoryInterface;
use App\Service\UrlNormalizer;
use Carbon\Carbon;
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
        private readonly Twig $twig,
        private readonly Messages $flash,
        private readonly RouteCollectorInterface $routeCollector,
        private readonly UrlRepositoryInterface $urlRepository,
        private readonly UrlNormalizer $urlNormalizer
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
        $url['name'] = $this->urlNormalizer->normalize($url['name']);

        $validator = new Validator($url);
        $validator->setPrependLabels(false);
        $validator->rule('required', 'name');
        $validator->rule('lengthMax', 'name', 255);
        $validator->rule('url', 'name');

        if ($validator->validate()) {
            if (!$existingUrl = $this->urlRepository->findOneByName($url['name'])) {
                $url['created_at'] = Carbon::now()->toDateTimeString();

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
        ];

        return $this->twig->render($response->withStatus(422), 'app/home.html.twig', $params);
    }
}
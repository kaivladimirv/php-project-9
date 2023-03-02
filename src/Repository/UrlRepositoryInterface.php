<?php

declare(strict_types=1);

namespace App\Repository;

use App\Exception\UrlNotFoundException;

interface UrlRepositoryInterface
{
    public function add(array $url): string;

    /**
     * @throws UrlNotFoundException
     */
    public function getOne(string $id): array;

    public function get(): array;

    public function findOneByName(string $name): ?array;
}

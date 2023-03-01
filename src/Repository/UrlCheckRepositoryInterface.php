<?php

declare(strict_types=1);

namespace App\Repository;

interface UrlCheckRepositoryInterface
{
    public function add(array $check): string;

    public function get(string $urlId): array;
}

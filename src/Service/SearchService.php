<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\Target;

class SearchService
{
    private Connection $manticoreConnection;

    public function __construct(
        #[Target('manticoreConnection')]
        Connection $manticoreConnection
    )
    {
        $this->manticoreConnection = $manticoreConnection;
    }

    public function searchProducts(string $query): array
    {
        // Используем SphinxQL синтаксис (MATCH) напрямую через DBAL
        $sql = 'SELECT id, MATCH(?) FROM products WHERE status = 1 OPTION ranker=bm25';

        return $this->manticoreConnection->fetchAllAssociative($sql, [$query]);
    }
}

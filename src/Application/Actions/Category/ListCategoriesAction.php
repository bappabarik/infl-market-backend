<?php
declare(strict_types=1);

namespace App\Application\Actions\Category;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use PDO;

class ListCategoriesAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private PDO $pdo,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $stmt = $this->pdo->query('SELECT * FROM categories ORDER BY name ASC');
        $categories = $stmt->fetchAll();

        return $this->respondWithData($categories);
    }
}
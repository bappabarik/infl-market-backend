<?php
declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use PDO;

class GetSettingsAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private PDO $pdo,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $stmt = $this->pdo->query('SELECT * FROM platform_settings ORDER BY setting_key');
        $rows = $stmt->fetchAll();

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $this->respondWithData($settings);
    }
}
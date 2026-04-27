<?php
declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Application\Actions\Action;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use PDO;

class UpdateSettingsAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private PDO $pdo,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        /** @var User $user */
        $user = $this->request->getAttribute('user');
        $data = (array)$this->request->getParsedBody();

        if (empty($data)) {
            throw new HttpBadRequestException($this->request, 'No settings provided');
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO platform_settings (setting_key, setting_value, updated_by)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by)'
        );

        foreach ($data as $key => $value) {
            $stmt->execute([(string)$key, (string)$value, $user->getId()]);
        }

        // Return all settings
        $all = $this->pdo->query('SELECT * FROM platform_settings ORDER BY setting_key')->fetchAll();
        $settings = [];
        foreach ($all as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $this->respondWithData($settings);
    }
}
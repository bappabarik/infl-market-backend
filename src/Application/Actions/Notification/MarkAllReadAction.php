<?php
declare(strict_types=1);

namespace App\Application\Actions\Notification;

use App\Application\Actions\Action;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use PDO;

class MarkAllReadAction extends Action
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

        $stmt = $this->pdo->prepare(
            'UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL'
        );
        $stmt->execute([$user->getId()]);

        return $this->respondWithData([
            'message' => $stmt->rowCount() . ' notification(s) marked as read',
        ]);
    }
}
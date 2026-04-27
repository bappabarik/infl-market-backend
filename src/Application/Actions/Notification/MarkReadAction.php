<?php
declare(strict_types=1);

namespace App\Application\Actions\Notification;

use App\Application\Actions\Action;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use PDO;

class MarkReadAction extends Action
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
        $notifId = (int)$this->resolveArg('id');

        $stmt = $this->pdo->prepare(
            'UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ? AND read_at IS NULL'
        );
        $stmt->execute([$notifId, $user->getId()]);

        if ($stmt->rowCount() === 0) {
            throw new HttpBadRequestException($this->request, 'Notification not found or already read');
        }

        return $this->respondWithData(['message' => 'Marked as read']);
    }
}
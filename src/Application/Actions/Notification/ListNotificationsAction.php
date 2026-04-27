<?php
declare(strict_types=1);

namespace App\Application\Actions\Notification;

use App\Application\Actions\Action;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use PDO;

class ListNotificationsAction extends Action
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

        $params = $this->request->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = min(50, max(1, (int)($params['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $stmt = $this->pdo->prepare(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?'
        );
        $stmt->execute([$user->getId(), $limit, $offset]);
        $notifications = $stmt->fetchAll();

        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ?');
        $countStmt->execute([$user->getId()]);
        $total = (int)$countStmt->fetchColumn();

        $unreadStmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL'
        );
        $unreadStmt->execute([$user->getId()]);
        $unread = (int)$unreadStmt->fetchColumn();

        return $this->respondWithData([
            'notifications' => $notifications,
            'unread_count'  => $unread,
            'pagination'    => [
                'page'        => $page,
                'limit'       => $limit,
                'total'       => $total,
                'total_pages' => (int)ceil($total / $limit),
            ],
        ]);
    }
}
<?php
declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use PDO;

class ListUsersAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private PDO $pdo,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $params = $this->request->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = min(50, max(1, (int)($params['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $where = ['1=1'];
        $bindings = [];

        if (!empty($params['role'])) {
            $where[] = 'role = ?';
            $bindings[] = $params['role'];
        }
        if (!empty($params['status'])) {
            $where[] = 'status = ?';
            $bindings[] = $params['status'];
        }
        if (!empty($params['q'])) {
            $where[] = '(full_name LIKE ? OR email LIKE ?)';
            $q = '%' . $params['q'] . '%';
            $bindings[] = $q;
            $bindings[] = $q;
        }

        $whereClause = implode(' AND ', $where);
        $bindings[] = $limit;
        $bindings[] = $offset;

        $stmt = $this->pdo->prepare(
            "SELECT id, uuid, email, full_name, role, status, created_at, last_login_at
             FROM users WHERE {$whereClause} ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute($bindings);
        $users = $stmt->fetchAll();

        $countBindings = array_slice($bindings, 0, -2);
        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE {$whereClause}");
        $countStmt->execute($countBindings);
        $total = (int)$countStmt->fetchColumn();

        return $this->respondWithData([
            'users'      => $users,
            'pagination' => [
                'page'        => $page,
                'limit'       => $limit,
                'total'       => $total,
                'total_pages' => (int)ceil($total / $limit),
            ],
        ]);
    }
}
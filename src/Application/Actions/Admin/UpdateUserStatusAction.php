<?php
declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Application\Actions\Action;
use App\Application\Validation\RequestValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Slim\Exception\HttpNotFoundException;
use PDO;

class UpdateUserStatusAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private PDO $pdo,
        private RequestValidator $validator,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $userId = (int)$this->resolveArg('id');

        $data = $this->validator->validate(
            (array)$this->request->getParsedBody(),
            [
                'status' => v::notEmpty()->in(['active', 'suspended', 'deleted']),
            ]
        );

        $stmt = $this->pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stmt->execute([$data['status'], $userId]);

        if ($stmt->rowCount() === 0) {
            throw new HttpNotFoundException($this->request, 'User not found');
        }

        $user = $this->pdo->prepare('SELECT id, uuid, email, full_name, role, status FROM users WHERE id = ?');
        $user->execute([$userId]);

        return $this->respondWithData($user->fetch());
    }
}
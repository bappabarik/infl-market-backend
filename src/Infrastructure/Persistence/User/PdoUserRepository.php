<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\User;
use App\Domain\User\UserRepository;
use PDO;
use Ramsey\Uuid\Uuid;

class PdoUserRepository implements UserRepository
{
    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?User
    {
        return $this->fetchOne('SELECT * FROM users WHERE id = ? LIMIT 1', [$id]);
    }

    public function findByUuid(string $uuid): ?User
    {
        return $this->fetchOne('SELECT * FROM users WHERE uuid = ? LIMIT 1', [$uuid]);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->fetchOne('SELECT * FROM users WHERE email = ? LIMIT 1', [$email]);
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return (bool)$stmt->fetchColumn();
    }

    public function create(string $email, string $passwordHash, string $fullName, string $role): User
    {
        $uuid = Uuid::uuid4()->toString();
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (uuid, email, password_hash, full_name, role, status)
             VALUES (?, ?, ?, ?, ?, "active")'
        );
        $stmt->execute([$uuid, $email, $passwordHash, $fullName, $role]);
        $id = (int)$this->pdo->lastInsertId();
        return new User($id, $uuid, $email, $passwordHash, $fullName, $role, 'active');
    }

    private function fetchOne(string $sql, array $params): ?User
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        if (!$row) return null;
        return new User(
            (int)$row['id'],
            $row['uuid'],
            $row['email'],
            $row['password_hash'],
            $row['full_name'],
            $row['role'],
            $row['status'],
        );
    }
}
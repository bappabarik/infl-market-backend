<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth;

use App\Domain\Auth\RefreshTokenRepository;
use PDO;

class PdoRefreshTokenRepository implements RefreshTokenRepository
{
    public function __construct(private PDO $pdo) {}

    public function store(int $userId, string $tokenHash, int $ttlSeconds, ?string $userAgent, ?string $ip): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO refresh_tokens (user_id, token_hash, user_agent, ip_address, expires_at)
             VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))'
        );
        $stmt->execute([$userId, $tokenHash, $userAgent, $ip, $ttlSeconds]);
    }

    public function findValid(string $tokenHash): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM refresh_tokens
             WHERE token_hash = ?
               AND revoked_at IS NULL
               AND expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute([$tokenHash]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function revoke(string $tokenHash): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE refresh_tokens SET revoked_at = NOW() WHERE token_hash = ?'
        );
        $stmt->execute([$tokenHash]);
    }

    public function revokeAllForUser(int $userId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE refresh_tokens SET revoked_at = NOW()
             WHERE user_id = ? AND revoked_at IS NULL'
        );
        $stmt->execute([$userId]);
    }
}
<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Influencer;

use App\Domain\Influencer\SocialAccountRepository;
use PDO;

class PdoSocialAccountRepository implements SocialAccountRepository
{
    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM influencer_social_accounts WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByInfluencerId(int $influencerId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM influencer_social_accounts WHERE influencer_id = ? ORDER BY is_primary DESC, followers_count DESC'
        );
        $stmt->execute([$influencerId]);
        return $stmt->fetchAll();
    }

    public function create(int $influencerId, array $data): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO influencer_social_accounts
             (influencer_id, platform, handle, profile_url, followers_count, is_primary)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $influencerId,
            $data['platform'],
            $data['handle'],
            $data['profile_url'] ?? null,
            $data['followers_count'] ?? 0,
            $data['is_primary'] ?? false,
        ]);
        return $this->findById((int)$this->pdo->lastInsertId());
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM influencer_social_accounts WHERE id = ?')->execute([$id]);
    }

    public function belongsToInfluencer(int $accountId, int $influencerId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM influencer_social_accounts WHERE id = ? AND influencer_id = ? LIMIT 1'
        );
        $stmt->execute([$accountId, $influencerId]);
        return (bool)$stmt->fetchColumn();
    }

    public function getRecentPosts(int $accountId, int $limit = 10): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM influencer_posts WHERE social_account_id = ? ORDER BY posted_at DESC LIMIT ?'
        );
        $stmt->execute([$accountId, $limit]);
        return $stmt->fetchAll();
    }
}
<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Influencer;

use App\Domain\Influencer\Influencer;
use App\Domain\Influencer\InfluencerRepository;
use PDO;

class PdoInfluencerRepository implements InfluencerRepository
{
    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?Influencer
    {
        return $this->fetchOne('SELECT * FROM influencers WHERE id = ? LIMIT 1', [$id]);
    }

    public function findByUserId(int $userId): ?Influencer
    {
        return $this->fetchOne('SELECT * FROM influencers WHERE user_id = ? LIMIT 1', [$userId]);
    }

    public function create(int $userId, string $displayName, ?int $affiliateId = null): Influencer
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO influencers (user_id, affiliate_id, display_name, base_cost_per_post, base_cost_per_video, currency, is_verified)
             VALUES (?, ?, ?, 0, 0, "INR", 0)'
        );
        $stmt->execute([$userId, $affiliateId, $displayName]);
        return $this->findById((int)$this->pdo->lastInsertId());
    }

    public function update(int $id, array $data): Influencer
    {
        $allowed = [
            'display_name', 'bio', 'category_id', 'location',
            'languages', 'base_cost_per_post', 'base_cost_per_video', 'currency',
        ];
        $sets = [];
        $values = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $sets[] = "{$key} = ?";
                if ($key === 'languages' && is_array($value)) {
                    $values[] = json_encode($value);
                } else {
                    $values[] = $value;
                }
            }
        }

        if (!empty($sets)) {
            $values[] = $id;
            $sql = 'UPDATE influencers SET ' . implode(', ', $sets) . ' WHERE id = ?';
            $this->pdo->prepare($sql)->execute($values);
        }

        return $this->findById($id);
    }

    public function search(array $filters, int $limit = 20, int $offset = 0): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = 'i.category_id = ?';
            $params[] = (int)$filters['category_id'];
        }

        if (!empty($filters['min_followers'])) {
            $where[] = 'EXISTS (SELECT 1 FROM influencer_social_accounts sa WHERE sa.influencer_id = i.id AND sa.followers_count >= ?)';
            $params[] = (int)$filters['min_followers'];
        }

        if (!empty($filters['max_price'])) {
            $where[] = 'i.base_cost_per_post <= ?';
            $params[] = (float)$filters['max_price'];
        }

        if (!empty($filters['platform'])) {
            $where[] = 'EXISTS (SELECT 1 FROM influencer_social_accounts sa WHERE sa.influencer_id = i.id AND sa.platform = ?)';
            $params[] = $filters['platform'];
        }

        if (!empty($filters['q'])) {
            $where[] = '(i.display_name LIKE ? OR i.bio LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params[] = $q;
            $params[] = $q;
        }

        $whereClause = implode(' AND ', $where);
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare(
            "SELECT i.*, u.email
             FROM influencers i
             JOIN users u ON u.id = i.user_id AND u.status = 'active'
             WHERE {$whereClause}
             ORDER BY i.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countSearch(array $filters): int
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = 'i.category_id = ?';
            $params[] = (int)$filters['category_id'];
        }
        if (!empty($filters['min_followers'])) {
            $where[] = 'EXISTS (SELECT 1 FROM influencer_social_accounts sa WHERE sa.influencer_id = i.id AND sa.followers_count >= ?)';
            $params[] = (int)$filters['min_followers'];
        }
        if (!empty($filters['max_price'])) {
            $where[] = 'i.base_cost_per_post <= ?';
            $params[] = (float)$filters['max_price'];
        }
        if (!empty($filters['platform'])) {
            $where[] = 'EXISTS (SELECT 1 FROM influencer_social_accounts sa WHERE sa.influencer_id = i.id AND sa.platform = ?)';
            $params[] = $filters['platform'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(i.display_name LIKE ? OR i.bio LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params[] = $q;
            $params[] = $q;
        }

        $whereClause = implode(' AND ', $where);
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM influencers i
             JOIN users u ON u.id = i.user_id AND u.status = 'active'
             WHERE {$whereClause}"
        );
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function findByAffiliateId(int $affiliateId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM influencers WHERE affiliate_id = ? ORDER BY created_at DESC');
        $stmt->execute([$affiliateId]);
        return array_map(fn($row) => $this->hydrate($row), $stmt->fetchAll());
    }

    private function fetchOne(string $sql, array $params): ?Influencer
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        if (!$row) return null;
        return $this->hydrate($row);
    }

    private function hydrate(array $row): Influencer
    {
        return new Influencer(
            (int)$row['id'],
            (int)$row['user_id'],
            $row['affiliate_id'] ? (int)$row['affiliate_id'] : null,
            $row['display_name'],
            $row['bio'],
            $row['category_id'] ? (int)$row['category_id'] : null,
            $row['location'],
            $row['languages'] ? json_decode($row['languages'], true) : null,
            (float)$row['base_cost_per_post'],
            (float)$row['base_cost_per_video'],
            $row['currency'],
            (bool)$row['is_verified'],
            $row['created_at'],
            $row['updated_at'],
        );
    }
}
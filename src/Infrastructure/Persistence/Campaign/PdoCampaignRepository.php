<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Campaign;

use App\Domain\Campaign\Campaign;
use App\Domain\Campaign\CampaignRepository;
use PDO;

class PdoCampaignRepository implements CampaignRepository
{
    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?Campaign
    {
        $stmt = $this->pdo->prepare('SELECT * FROM campaigns WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function findByBrandId(int $brandId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM campaigns WHERE brand_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$brandId]);
        return array_map(fn($row) => $this->hydrate($row)->jsonSerialize(), $stmt->fetchAll());
    }

    public function create(array $data): Campaign
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO campaigns (brand_id, product_id, title, brief, budget, start_date, end_date, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, "draft")'
        );
        $stmt->execute([
            $data['brand_id'],
            $data['product_id'] ?? null,
            $data['title'],
            $data['brief'] ?? null,
            $data['budget'] ?? 0,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
        ]);
        return $this->findById((int)$this->pdo->lastInsertId());
    }

    public function update(int $id, array $data): Campaign
    {
        $allowed = ['title', 'brief', 'budget', 'start_date', 'end_date', 'status', 'product_id'];
        $sets = [];
        $values = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $sets[] = "{$key} = ?";
                $values[] = $value;
            }
        }

        if (!empty($sets)) {
            $values[] = $id;
            $sql = 'UPDATE campaigns SET ' . implode(', ', $sets) . ' WHERE id = ?';
            $this->pdo->prepare($sql)->execute($values);
        }

        return $this->findById($id);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare("UPDATE campaigns SET status = 'cancelled' WHERE id = ?")->execute([$id]);
    }

    public function belongsToBrand(int $campaignId, int $brandId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM campaigns WHERE id = ? AND brand_id = ? LIMIT 1');
        $stmt->execute([$campaignId, $brandId]);
        return (bool)$stmt->fetchColumn();
    }

    private function hydrate(array $row): Campaign
    {
        return new Campaign(
            (int)$row['id'],
            (int)$row['brand_id'],
            $row['product_id'] ? (int)$row['product_id'] : null,
            $row['title'],
            $row['brief'],
            (float)$row['budget'],
            $row['start_date'],
            $row['end_date'],
            $row['status'],
            $row['created_at'],
            $row['updated_at'],
        );
    }
}
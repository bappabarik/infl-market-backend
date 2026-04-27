<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Brand;

use App\Domain\Brand\BrandProductRepository;
use PDO;

class PdoBrandProductRepository implements BrandProductRepository
{
    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM brand_products WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByBrandId(int $brandId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM brand_products WHERE brand_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$brandId]);
        return $stmt->fetchAll();
    }

    public function create(int $brandId, string $title, ?string $description, ?string $campaignGoal): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO brand_products (brand_id, title, description, campaign_goal, status)
             VALUES (?, ?, ?, ?, "draft")'
        );
        $stmt->execute([$brandId, $title, $description, $campaignGoal]);
        return $this->findById((int)$this->pdo->lastInsertId());
    }

    public function update(int $id, array $data): array
    {
        $allowed = ['title', 'description', 'campaign_goal', 'status'];
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
            $sql = 'UPDATE brand_products SET ' . implode(', ', $sets) . ' WHERE id = ?';
            $this->pdo->prepare($sql)->execute($values);
        }

        return $this->findById($id);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('UPDATE brand_products SET status = "archived" WHERE id = ?')
             ->execute([$id]);
    }

    public function belongsToBrand(int $productId, int $brandId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM brand_products WHERE id = ? AND brand_id = ? LIMIT 1'
        );
        $stmt->execute([$productId, $brandId]);
        return (bool)$stmt->fetchColumn();
    }
}
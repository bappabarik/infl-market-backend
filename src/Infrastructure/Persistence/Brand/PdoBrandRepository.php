<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Brand;

use App\Domain\Brand\Brand;
use App\Domain\Brand\BrandRepository;
use PDO;

class PdoBrandRepository implements BrandRepository
{
    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?Brand
    {
        return $this->fetchOne('SELECT * FROM brands WHERE id = ? LIMIT 1', [$id]);
    }

    public function findByUserId(int $userId): ?Brand
    {
        return $this->fetchOne('SELECT * FROM brands WHERE user_id = ? LIMIT 1', [$userId]);
    }

    public function create(int $userId, string $name, ?string $logoUrl, ?string $description, ?string $website, ?int $industryId): Brand
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO brands (user_id, name, logo_url, description, website, industry_id)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $name, $logoUrl, $description, $website, $industryId]);
        return $this->findById((int)$this->pdo->lastInsertId());
    }

    public function update(int $id, array $data): Brand
    {
        $allowed = ['name', 'logo_url', 'description', 'website', 'industry_id'];
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
            $sql = 'UPDATE brands SET ' . implode(', ', $sets) . ' WHERE id = ?';
            $this->pdo->prepare($sql)->execute($values);
        }

        return $this->findById($id);
    }

    private function fetchOne(string $sql, array $params): ?Brand
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        if (!$row) return null;

        return new Brand(
            (int)$row['id'],
            (int)$row['user_id'],
            $row['name'],
            $row['logo_url'],
            $row['description'],
            $row['website'],
            $row['industry_id'] ? (int)$row['industry_id'] : null,
            $row['created_at'],
            $row['updated_at'],
        );
    }
}
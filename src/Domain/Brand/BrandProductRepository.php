<?php
declare(strict_types=1);

namespace App\Domain\Brand;

interface BrandProductRepository
{
    public function findById(int $id): ?array;
    public function findByBrandId(int $brandId): array;
    public function create(int $brandId, string $title, ?string $description, ?string $campaignGoal): array;
    public function update(int $id, array $data): array;
    public function delete(int $id): void;
    public function belongsToBrand(int $productId, int $brandId): bool;
}
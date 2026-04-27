<?php
declare(strict_types=1);

namespace App\Domain\Brand;

interface BrandRepository
{
    public function findById(int $id): ?Brand;
    public function findByUserId(int $userId): ?Brand;
    public function create(int $userId, string $name, ?string $logoUrl, ?string $description, ?string $website, ?int $industryId): Brand;
    public function update(int $id, array $data): Brand;
}
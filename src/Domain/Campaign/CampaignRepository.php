<?php
declare(strict_types=1);

namespace App\Domain\Campaign;

interface CampaignRepository
{
    public function findById(int $id): ?Campaign;
    public function findByBrandId(int $brandId): array;
    public function create(array $data): Campaign;
    public function update(int $id, array $data): Campaign;
    public function delete(int $id): void;
    public function belongsToBrand(int $campaignId, int $brandId): bool;
}
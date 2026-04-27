<?php
declare(strict_types=1);

namespace App\Domain\Affiliate;

interface AffiliateRepository
{
    public function findById(int $id): ?Affiliate;
    public function findByUserId(int $userId): ?Affiliate;
    public function create(int $userId, ?string $agencyName): Affiliate;
    public function update(int $id, array $data): Affiliate;
    public function getEarnings(int $affiliateId): array;
    public function getEarningsHistory(int $affiliateId, int $limit, int $offset): array;
    public function countEarningsHistory(int $affiliateId): int;
}
<?php
declare(strict_types=1);

namespace App\Domain\Influencer;

interface InfluencerRepository
{
    public function findById(int $id): ?Influencer;
    public function findByUserId(int $userId): ?Influencer;
    public function create(int $userId, string $displayName, ?int $affiliateId = null): Influencer;
    public function update(int $id, array $data): Influencer;
    public function search(array $filters, int $limit = 20, int $offset = 0): array;
    public function countSearch(array $filters): int;
    public function findByAffiliateId(int $affiliateId): array;
}
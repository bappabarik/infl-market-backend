<?php
declare(strict_types=1);

namespace App\Domain\Campaign;

interface DealRepository
{
    public function findById(int $id): ?array;
    public function findByCampaignId(int $campaignId): array;
    public function findByInfluencerId(int $influencerId): array;
    public function findByBrandId(int $brandId): array;
    public function findByAffiliateId(int $affiliateId): array;
    public function create(array $data): array;
    public function updateStatus(int $id, string $status): array;
    public function addDeliverable(int $dealId, array $data): array;
    public function getDeliverables(int $dealId): array;
}
<?php
declare(strict_types=1);

namespace App\Domain\Influencer;

interface SocialAccountRepository
{
    public function findById(int $id): ?array;
    public function findByInfluencerId(int $influencerId): array;
    public function create(int $influencerId, array $data): array;
    public function delete(int $id): void;
    public function belongsToInfluencer(int $accountId, int $influencerId): bool;
    public function getRecentPosts(int $accountId, int $limit = 10): array;
}
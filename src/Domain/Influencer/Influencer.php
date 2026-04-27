<?php
declare(strict_types=1);

namespace App\Domain\Influencer;

use JsonSerializable;

class Influencer implements JsonSerializable
{
    public function __construct(
        private ?int $id,
        private int $userId,
        private ?int $affiliateId,
        private string $displayName,
        private ?string $bio,
        private ?int $categoryId,
        private ?string $location,
        private ?array $languages,
        private float $baseCostPerPost,
        private float $baseCostPerVideo,
        private string $currency,
        private bool $isVerified,
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getAffiliateId(): ?int { return $this->affiliateId; }
    public function getDisplayName(): string { return $this->displayName; }
    public function getBio(): ?string { return $this->bio; }
    public function getCategoryId(): ?int { return $this->categoryId; }
    public function getBaseCostPerPost(): float { return $this->baseCostPerPost; }
    public function getBaseCostPerVideo(): float { return $this->baseCostPerVideo; }
    public function getCurrency(): string { return $this->currency; }
    public function isVerified(): bool { return $this->isVerified; }

    public function jsonSerialize(): array
    {
        return [
            'id'                 => $this->id,
            'user_id'            => $this->userId,
            'affiliate_id'       => $this->affiliateId,
            'display_name'       => $this->displayName,
            'bio'                => $this->bio,
            'category_id'        => $this->categoryId,
            'location'           => $this->location,
            'languages'          => $this->languages,
            'base_cost_per_post' => $this->baseCostPerPost,
            'base_cost_per_video'=> $this->baseCostPerVideo,
            'currency'           => $this->currency,
            'is_verified'        => $this->isVerified,
            'created_at'         => $this->createdAt,
            'updated_at'         => $this->updatedAt,
        ];
    }
}
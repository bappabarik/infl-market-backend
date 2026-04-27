<?php
declare(strict_types=1);

namespace App\Domain\Brand;

use JsonSerializable;

class Brand implements JsonSerializable
{
    public function __construct(
        private ?int $id,
        private int $userId,
        private string $name,
        private ?string $logoUrl,
        private ?string $description,
        private ?string $website,
        private ?int $industryId,
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getName(): string { return $this->name; }
    public function getLogoUrl(): ?string { return $this->logoUrl; }
    public function getDescription(): ?string { return $this->description; }
    public function getWebsite(): ?string { return $this->website; }
    public function getIndustryId(): ?int { return $this->industryId; }

    public function jsonSerialize(): array
    {
        return [
            'id'          => $this->id,
            'user_id'     => $this->userId,
            'name'        => $this->name,
            'logo_url'    => $this->logoUrl,
            'description' => $this->description,
            'website'     => $this->website,
            'industry_id' => $this->industryId,
            'created_at'  => $this->createdAt,
            'updated_at'  => $this->updatedAt,
        ];
    }
}
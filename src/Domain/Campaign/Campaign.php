<?php
declare(strict_types=1);

namespace App\Domain\Campaign;

use JsonSerializable;

class Campaign implements JsonSerializable
{
    public function __construct(
        private ?int $id,
        private int $brandId,
        private ?int $productId,
        private string $title,
        private ?string $brief,
        private float $budget,
        private ?string $startDate,
        private ?string $endDate,
        private string $status,
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getBrandId(): int { return $this->brandId; }
    public function getStatus(): string { return $this->status; }

    public function jsonSerialize(): array
    {
        return [
            'id'         => $this->id,
            'brand_id'   => $this->brandId,
            'product_id' => $this->productId,
            'title'      => $this->title,
            'brief'      => $this->brief,
            'budget'     => $this->budget,
            'start_date' => $this->startDate,
            'end_date'   => $this->endDate,
            'status'     => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
<?php
declare(strict_types=1);

namespace App\Domain\Affiliate;

use JsonSerializable;

class Affiliate implements JsonSerializable
{
    public function __construct(
        private ?int $id,
        private int $userId,
        private ?string $agencyName,
        private string $referralCode,
        private float $defaultCommissionPercent,
        private float $totalEarnings,
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getAgencyName(): ?string { return $this->agencyName; }
    public function getReferralCode(): string { return $this->referralCode; }
    public function getDefaultCommissionPercent(): float { return $this->defaultCommissionPercent; }
    public function getTotalEarnings(): float { return $this->totalEarnings; }

    public function jsonSerialize(): array
    {
        return [
            'id'                         => $this->id,
            'user_id'                    => $this->userId,
            'agency_name'                => $this->agencyName,
            'referral_code'              => $this->referralCode,
            'default_commission_percent' => $this->defaultCommissionPercent,
            'total_earnings'             => $this->totalEarnings,
            'created_at'                 => $this->createdAt,
            'updated_at'                 => $this->updatedAt,
        ];
    }
}
<?php
declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;

class User implements JsonSerializable
{
    public function __construct(
        private ?int $id,
        private string $uuid,
        private string $email,
        private string $passwordHash,
        private string $fullName,
        private string $role,
        private string $status = 'active',
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getUuid(): string { return $this->uuid; }
    public function getEmail(): string { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getFullName(): string { return $this->fullName; }
    public function getRole(): string { return $this->role; }
    public function getStatus(): string { return $this->status; }
    public function isActive(): bool { return $this->status === 'active'; }

    public function jsonSerialize(): array
    {
        return [
            'id'    => $this->uuid,
            'email' => $this->email,
            'name'  => $this->fullName,
            'role'  => $this->role,
        ];
    }
}
<?php
declare(strict_types=1);

namespace App\Domain\User;

interface UserRepository
{
    public function findById(int $id): ?User;
    public function findByUuid(string $uuid): ?User;
    public function findByEmail(string $email): ?User;
    public function create(string $email, string $passwordHash, string $fullName, string $role): User;
    public function emailExists(string $email): bool;
}
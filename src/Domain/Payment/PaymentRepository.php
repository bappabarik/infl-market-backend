<?php
declare(strict_types=1);

namespace App\Domain\Payment;

interface PaymentRepository
{
    public function findById(int $id): ?array;
    public function findByDealId(int $dealId): ?array;
    public function create(array $data): array;
    public function updateStatus(int $id, string $status): array;
    public function createPayouts(int $paymentId, int $dealId, array $deal): void;
    public function getPayoutsByDealId(int $dealId): array;
    public function getAllPayments(int $limit, int $offset): array;
    public function countAllPayments(): int;
    public function findPayoutById(int $id): ?array;
    public function updatePayoutStatus(int $id, string $status): array;
}
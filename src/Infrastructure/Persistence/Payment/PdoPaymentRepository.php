<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Payment;

use App\Domain\Payment\PaymentRepository;
use PDO;

class PdoPaymentRepository implements PaymentRepository
{
    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, cd.campaign_id, cd.influencer_id, cd.platform
             FROM payments p
             JOIN campaign_deals cd ON cd.id = p.deal_id
             WHERE p.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByDealId(int $dealId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM payments WHERE deal_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$dealId]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO payments (deal_id, brand_id, gateway, gateway_txn_id, amount, currency, status, paid_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['deal_id'],
            $data['brand_id'],
            $data['gateway'],
            $data['gateway_txn_id'] ?? null,
            $data['amount'],
            $data['currency'] ?? 'INR',
            $data['status'] ?? 'initiated',
            $data['paid_at'] ?? null,
        ]);
        return $this->findById((int)$this->pdo->lastInsertId());
    }

    public function updateStatus(int $id, string $status): array
    {
        $paidAt = $status === 'success' ? date('Y-m-d H:i:s') : null;
        if ($paidAt) {
            $this->pdo->prepare('UPDATE payments SET status = ?, paid_at = ? WHERE id = ?')
                 ->execute([$status, $paidAt, $id]);
        } else {
            $this->pdo->prepare('UPDATE payments SET status = ? WHERE id = ?')
                 ->execute([$status, $id]);
        }
        return $this->findById($id);
    }

    public function createPayouts(int $paymentId, int $dealId, array $deal): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO payouts (payment_id, deal_id, payee_type, payee_user_id, amount, status)
             VALUES (?, ?, ?, ?, ?, "pending")'
        );

        // Influencer payout
        $infUserId = $this->pdo->prepare('SELECT user_id FROM influencers WHERE id = ?');
        $infUserId->execute([(int)$deal['influencer_id']]);
        $infUser = $infUserId->fetchColumn();

        $stmt->execute([
            $paymentId, $dealId, 'influencer', $infUser,
            (float)$deal['influencer_base_cost'] * (int)$deal['quantity'],
        ]);

        // Affiliate payout (if applicable)
        if (!empty($deal['affiliate_id']) && (float)$deal['affiliate_commission_amount'] > 0) {
            $affUserId = $this->pdo->prepare('SELECT user_id FROM affiliates WHERE id = ?');
            $affUserId->execute([(int)$deal['affiliate_id']]);
            $affUser = $affUserId->fetchColumn();

            $stmt->execute([
                $paymentId, $dealId, 'affiliate', $affUser,
                (float)$deal['affiliate_commission_amount'],
            ]);
        }

        // Platform payout
        $stmt->execute([
            $paymentId, $dealId, 'platform', null,
            (float)$deal['platform_commission_amount'],
        ]);
    }

    public function getPayoutsByDealId(int $dealId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM payouts WHERE deal_id = ? ORDER BY id');
        $stmt->execute([$dealId]);
        return $stmt->fetchAll();
    }

    public function getAllPayments(int $limit, int $offset): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, cd.campaign_id, cd.influencer_id, cd.platform, cd.content_type
             FROM payments p
             JOIN campaign_deals cd ON cd.id = p.deal_id
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?'
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function countAllPayments(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM payments')->fetchColumn();
    }

    public function findPayoutById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM payouts WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function updatePayoutStatus(int $id, string $status): array
    {
        $paidAt = $status === 'paid' ? date('Y-m-d H:i:s') : null;
        if ($paidAt) {
            $this->pdo->prepare('UPDATE payouts SET status = ?, paid_at = ? WHERE id = ?')
                 ->execute([$status, $paidAt, $id]);
        } else {
            $this->pdo->prepare('UPDATE payouts SET status = ? WHERE id = ?')
                 ->execute([$status, $id]);
        }
        return $this->findPayoutById($id);
    }
}
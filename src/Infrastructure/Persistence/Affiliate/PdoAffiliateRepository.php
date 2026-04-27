<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Affiliate;

use App\Domain\Affiliate\Affiliate;
use App\Domain\Affiliate\AffiliateRepository;
use PDO;

class PdoAffiliateRepository implements AffiliateRepository
{
    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?Affiliate
    {
        return $this->fetchOne('SELECT * FROM affiliates WHERE id = ? LIMIT 1', [$id]);
    }

    public function findByUserId(int $userId): ?Affiliate
    {
        return $this->fetchOne('SELECT * FROM affiliates WHERE user_id = ? LIMIT 1', [$userId]);
    }

    public function create(int $userId, ?string $agencyName): Affiliate
    {
        $referralCode = strtoupper(substr(md5((string)$userId . time()), 0, 8));

        $stmt = $this->pdo->prepare(
            'INSERT INTO affiliates (user_id, agency_name, referral_code, default_commission_percent, total_earnings)
             VALUES (?, ?, ?, 10.00, 0.00)'
        );
        $stmt->execute([$userId, $agencyName, $referralCode]);
        return $this->findById((int)$this->pdo->lastInsertId());
    }

    public function update(int $id, array $data): Affiliate
    {
        $allowed = ['agency_name', 'default_commission_percent'];
        $sets = [];
        $values = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $sets[] = "{$key} = ?";
                $values[] = $value;
            }
        }

        if (!empty($sets)) {
            $values[] = $id;
            $sql = 'UPDATE affiliates SET ' . implode(', ', $sets) . ' WHERE id = ?';
            $this->pdo->prepare($sql)->execute($values);
        }

        return $this->findById($id);
    }

    public function getEarnings(int $affiliateId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                COALESCE(SUM(p.amount), 0) as total_earned,
                COALESCE(SUM(CASE WHEN p.status = "paid" THEN p.amount ELSE 0 END), 0) as total_paid,
                COALESCE(SUM(CASE WHEN p.status = "pending" THEN p.amount ELSE 0 END), 0) as total_pending,
                COUNT(*) as total_deals
             FROM payouts p
             WHERE p.payee_type = "affiliate"
               AND p.payee_user_id = (SELECT user_id FROM affiliates WHERE id = ?)'
        );
        $stmt->execute([$affiliateId]);
        return $stmt->fetch();
    }

    public function getEarningsHistory(int $affiliateId, int $limit, int $offset): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, cd.campaign_id, cd.influencer_id, cd.platform, cd.content_type
             FROM payouts p
             JOIN campaign_deals cd ON cd.id = p.deal_id
             WHERE p.payee_type = "affiliate"
               AND p.payee_user_id = (SELECT user_id FROM affiliates WHERE id = ?)
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?'
        );
        $stmt->execute([$affiliateId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public function countEarningsHistory(int $affiliateId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM payouts p
             WHERE p.payee_type = "affiliate"
               AND p.payee_user_id = (SELECT user_id FROM affiliates WHERE id = ?)'
        );
        $stmt->execute([$affiliateId]);
        return (int)$stmt->fetchColumn();
    }

    private function fetchOne(string $sql, array $params): ?Affiliate
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        if (!$row) return null;

        return new Affiliate(
            (int)$row['id'],
            (int)$row['user_id'],
            $row['agency_name'],
            $row['referral_code'],
            (float)$row['default_commission_percent'],
            (float)$row['total_earnings'],
            $row['created_at'],
            $row['updated_at'],
        );
    }
}
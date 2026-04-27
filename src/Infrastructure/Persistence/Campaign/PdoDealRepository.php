<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Campaign;

use App\Domain\Campaign\DealRepository;
use PDO;

class PdoDealRepository implements DealRepository
{
    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT cd.*, c.title as campaign_title, c.brand_id,
                    i.display_name as influencer_name
             FROM campaign_deals cd
             JOIN campaigns c ON c.id = cd.campaign_id
             JOIN influencers i ON i.id = cd.influencer_id
             WHERE cd.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByCampaignId(int $campaignId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT cd.*, i.display_name as influencer_name
             FROM campaign_deals cd
             JOIN influencers i ON i.id = cd.influencer_id
             WHERE cd.campaign_id = ?
             ORDER BY cd.created_at DESC'
        );
        $stmt->execute([$campaignId]);
        return $stmt->fetchAll();
    }

    public function findByInfluencerId(int $influencerId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT cd.*, c.title as campaign_title, c.brand_id
             FROM campaign_deals cd
             JOIN campaigns c ON c.id = cd.campaign_id
             WHERE cd.influencer_id = ?
             ORDER BY cd.created_at DESC'
        );
        $stmt->execute([$influencerId]);
        return $stmt->fetchAll();
    }

    public function findByBrandId(int $brandId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT cd.*, c.title as campaign_title, i.display_name as influencer_name
             FROM campaign_deals cd
             JOIN campaigns c ON c.id = cd.campaign_id
             JOIN influencers i ON i.id = cd.influencer_id
             WHERE c.brand_id = ?
             ORDER BY cd.created_at DESC'
        );
        $stmt->execute([$brandId]);
        return $stmt->fetchAll();
    }

    public function findByAffiliateId(int $affiliateId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT cd.*, c.title as campaign_title, i.display_name as influencer_name
             FROM campaign_deals cd
             JOIN campaigns c ON c.id = cd.campaign_id
             JOIN influencers i ON i.id = cd.influencer_id
             WHERE cd.affiliate_id = ?
             ORDER BY cd.created_at DESC'
        );
        $stmt->execute([$affiliateId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO campaign_deals
             (campaign_id, influencer_id, affiliate_id, platform, content_type, quantity,
              influencer_base_cost, affiliate_commission_percent, platform_commission_percent,
              affiliate_commission_amount, platform_commission_amount, total_amount,
              status, deadline)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "pending", ?)'
        );
        $stmt->execute([
            $data['campaign_id'],
            $data['influencer_id'],
            $data['affiliate_id'],
            $data['platform'],
            $data['content_type'],
            $data['quantity'],
            $data['influencer_base_cost'],
            $data['affiliate_commission_percent'],
            $data['platform_commission_percent'],
            $data['affiliate_commission_amount'],
            $data['platform_commission_amount'],
            $data['total_amount'],
            $data['deadline'] ?? null,
        ]);
        return $this->findById((int)$this->pdo->lastInsertId());
    }

    public function updateStatus(int $id, string $status): array
    {
        $this->pdo->prepare('UPDATE campaign_deals SET status = ? WHERE id = ?')
             ->execute([$status, $id]);
        return $this->findById($id);
    }

    public function addDeliverable(int $dealId, array $data): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO deal_deliverables (deal_id, post_url, media_url, notes, submitted_at, status)
             VALUES (?, ?, ?, ?, NOW(), "submitted")'
        );
        $stmt->execute([
            $dealId,
            $data['post_url'] ?? null,
            $data['media_url'] ?? null,
            $data['notes'] ?? null,
        ]);

        $id = (int)$this->pdo->lastInsertId();
        $stmt = $this->pdo->prepare('SELECT * FROM deal_deliverables WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getDeliverables(int $dealId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM deal_deliverables WHERE deal_id = ? ORDER BY submitted_at DESC'
        );
        $stmt->execute([$dealId]);
        return $stmt->fetchAll();
    }
}
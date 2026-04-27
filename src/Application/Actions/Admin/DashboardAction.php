<?php
declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use PDO;

class DashboardAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private PDO $pdo,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $stats = [];

        // Revenue
        $rev = $this->pdo->query(
            "SELECT COALESCE(SUM(amount), 0) as total_revenue FROM payments WHERE status = 'success'"
        )->fetch();
        $stats['total_revenue'] = (float)$rev['total_revenue'];

        // Platform earnings
        $plat = $this->pdo->query(
            "SELECT COALESCE(SUM(amount), 0) as platform_earnings FROM payouts WHERE payee_type = 'platform'"
        )->fetch();
        $stats['platform_earnings'] = (float)$plat['platform_earnings'];

        // User counts
        $users = $this->pdo->query(
            "SELECT role, COUNT(*) as count FROM users WHERE status = 'active' GROUP BY role"
        )->fetchAll();
        $stats['users'] = [];
        foreach ($users as $u) {
            $stats['users'][$u['role']] = (int)$u['count'];
        }

        // Campaign stats
        $campaigns = $this->pdo->query(
            "SELECT status, COUNT(*) as count FROM campaigns GROUP BY status"
        )->fetchAll();
        $stats['campaigns'] = [];
        foreach ($campaigns as $c) {
            $stats['campaigns'][$c['status']] = (int)$c['count'];
        }

        // Deal stats
        $deals = $this->pdo->query(
            "SELECT status, COUNT(*) as count FROM campaign_deals GROUP BY status"
        )->fetchAll();
        $stats['deals'] = [];
        foreach ($deals as $d) {
            $stats['deals'][$d['status']] = (int)$d['count'];
        }

        // Top influencers by deals
        $topInf = $this->pdo->query(
            "SELECT i.display_name, COUNT(cd.id) as deal_count, SUM(cd.total_amount) as total_value
             FROM influencers i
             JOIN campaign_deals cd ON cd.influencer_id = i.id
             GROUP BY i.id, i.display_name
             ORDER BY total_value DESC
             LIMIT 10"
        )->fetchAll();
        $stats['top_influencers'] = $topInf;

        // Top brands by spend
        $topBrands = $this->pdo->query(
            "SELECT b.name, COUNT(p.id) as payment_count, SUM(p.amount) as total_spent
             FROM brands b
             JOIN payments p ON p.brand_id = b.id AND p.status = 'success'
             GROUP BY b.id, b.name
             ORDER BY total_spent DESC
             LIMIT 10"
        )->fetchAll();
        $stats['top_brands'] = $topBrands;

        return $this->respondWithData($stats);
    }
}
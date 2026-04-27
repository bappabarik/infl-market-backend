<?php
declare(strict_types=1);

namespace App\Application\Actions\Review;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use PDO;

class GetInfluencerReviewsAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private PDO $pdo,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $influencerId = (int)$this->resolveArg('id');

        $stmt = $this->pdo->prepare(
            'SELECT r.*, u.full_name as reviewer_name
             FROM reviews r
             JOIN users u ON u.id = r.reviewer_user_id
             JOIN influencers i ON i.user_id = r.reviewee_user_id
             WHERE i.id = ?
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([$influencerId]);
        $reviews = $stmt->fetchAll();

        // Calculate average
        $avg = $this->pdo->prepare(
            'SELECT AVG(r.rating) as avg_rating, COUNT(*) as total_reviews
             FROM reviews r
             JOIN influencers i ON i.user_id = r.reviewee_user_id
             WHERE i.id = ?'
        );
        $avg->execute([$influencerId]);
        $stats = $avg->fetch();

        return $this->respondWithData([
            'reviews'       => $reviews,
            'avg_rating'    => round((float)($stats['avg_rating'] ?? 0), 1),
            'total_reviews' => (int)($stats['total_reviews'] ?? 0),
        ]);
    }
}
<?php
declare(strict_types=1);

namespace App\Application\Actions\Review;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use PDO;

class GetBrandReviewsAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private PDO $pdo,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $brandId = (int)$this->resolveArg('id');

        $stmt = $this->pdo->prepare(
            'SELECT r.*, u.full_name as reviewer_name
             FROM reviews r
             JOIN users u ON u.id = r.reviewer_user_id
             JOIN brands b ON b.user_id = r.reviewee_user_id
             WHERE b.id = ?
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([$brandId]);
        $reviews = $stmt->fetchAll();

        $avg = $this->pdo->prepare(
            'SELECT AVG(r.rating) as avg_rating, COUNT(*) as total_reviews
             FROM reviews r
             JOIN brands b ON b.user_id = r.reviewee_user_id
             WHERE b.id = ?'
        );
        $avg->execute([$brandId]);
        $stats = $avg->fetch();

        return $this->respondWithData([
            'reviews'       => $reviews,
            'avg_rating'    => round((float)($stats['avg_rating'] ?? 0), 1),
            'total_reviews' => (int)($stats['total_reviews'] ?? 0),
        ]);
    }
}
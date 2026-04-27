<?php
declare(strict_types=1);

namespace App\Application\Actions\Review;

use App\Application\Actions\Action;
use App\Application\Validation\RequestValidator;
use App\Domain\Campaign\DealRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use PDO;

class CreateReviewAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private DealRepository $deals,
        private RequestValidator $validator,
        private PDO $pdo,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        /** @var User $user */
        $user = $this->request->getAttribute('user');
        $dealId = (int)$this->resolveArg('id');
        $deal = $this->deals->findById($dealId);

        if (!$deal) {
            throw new HttpNotFoundException($this->request, 'Deal not found');
        }

        if ($deal['status'] !== 'completed') {
            throw new HttpBadRequestException($this->request, 'Deal must be completed before reviewing');
        }

        $data = $this->validator->validate(
            (array)$this->request->getParsedBody(),
            [
                'rating'  => v::notEmpty()->intType()->between(1, 5),
                'comment' => v::optional(v::stringType()->length(1, 5000)),
            ]
        );

        // Determine reviewee based on reviewer's role
        if ($user->getRole() === 'brand') {
            // Brand reviews the influencer
            $revieweeStmt = $this->pdo->prepare('SELECT user_id FROM influencers WHERE id = ?');
            $revieweeStmt->execute([(int)$deal['influencer_id']]);
        } else {
            // Influencer reviews the brand
            $revieweeStmt = $this->pdo->prepare('SELECT user_id FROM brands WHERE id = ?');
            $revieweeStmt->execute([(int)$deal['brand_id']]);
        }
        $revieweeUserId = (int)$revieweeStmt->fetchColumn();

        // Check if already reviewed
        $exists = $this->pdo->prepare(
            'SELECT 1 FROM reviews WHERE deal_id = ? AND reviewer_user_id = ? LIMIT 1'
        );
        $exists->execute([$dealId, $user->getId()]);
        if ($exists->fetchColumn()) {
            throw new HttpBadRequestException($this->request, 'You have already reviewed this deal');
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO reviews (deal_id, reviewer_user_id, reviewee_user_id, rating, comment)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $dealId,
            $user->getId(),
            $revieweeUserId,
            $data['rating'],
            $data['comment'] ?? null,
        ]);

        $reviewId = (int)$this->pdo->lastInsertId();
        $review = $this->pdo->prepare('SELECT * FROM reviews WHERE id = ?');
        $review->execute([$reviewId]);

        return $this->respondWithData($review->fetch(), 201);
    }
}
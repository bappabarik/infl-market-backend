<?php
declare(strict_types=1);

namespace App\Application\Actions\Affiliate;

use App\Application\Actions\Action;
use App\Domain\Affiliate\AffiliateRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

class GetEarningsHistoryAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private AffiliateRepository $affiliates,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        /** @var User $user */
        $user = $this->request->getAttribute('user');
        $affiliate = $this->affiliates->findByUserId($user->getId());

        if (!$affiliate) {
            throw new HttpNotFoundException($this->request, 'Affiliate profile not found');
        }

        $params = $this->request->getQueryParams();
        $page   = max(1, (int)($params['page'] ?? 1));
        $limit  = min(50, max(1, (int)($params['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $history = $this->affiliates->getEarningsHistory($affiliate->getId(), $limit, $offset);
        $total   = $this->affiliates->countEarningsHistory($affiliate->getId());

        return $this->respondWithData([
            'history'    => $history,
            'pagination' => [
                'page'        => $page,
                'limit'       => $limit,
                'total'       => $total,
                'total_pages' => (int)ceil($total / $limit),
            ],
        ]);
    }
}
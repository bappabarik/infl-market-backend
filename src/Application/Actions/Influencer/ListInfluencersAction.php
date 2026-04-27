<?php
declare(strict_types=1);

namespace App\Application\Actions\Influencer;

use App\Application\Actions\Action;
use App\Domain\Influencer\InfluencerRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class ListInfluencersAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private InfluencerRepository $influencers,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $params = $this->request->getQueryParams();

        $filters = [
            'category_id'   => $params['category_id'] ?? null,
            'min_followers'  => $params['min_followers'] ?? null,
            'max_price'      => $params['max_price'] ?? null,
            'platform'       => $params['platform'] ?? null,
            'q'              => $params['q'] ?? null,
        ];

        $page  = max(1, (int)($params['page'] ?? 1));
        $limit = min(50, max(1, (int)($params['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $influencers = $this->influencers->search($filters, $limit, $offset);
        $total = $this->influencers->countSearch($filters);

        return $this->respondWithData([
            'influencers' => $influencers,
            'pagination'  => [
                'page'       => $page,
                'limit'      => $limit,
                'total'      => $total,
                'total_pages'=> (int)ceil($total / $limit),
            ],
        ]);
    }
}
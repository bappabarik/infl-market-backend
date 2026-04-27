<?php
declare(strict_types=1);

namespace App\Application\Actions\Affiliate;

use App\Application\Actions\Action;
use App\Domain\Affiliate\AffiliateRepository;
use App\Domain\Influencer\InfluencerRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

class ListAffiliateInfluencersAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private AffiliateRepository $affiliates,
        private InfluencerRepository $influencers,
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

        $influencers = $this->influencers->findByAffiliateId($affiliate->getId());

        return $this->respondWithData([
            'influencers' => array_map(fn($i) => $i->jsonSerialize(), $influencers),
        ]);
    }
}
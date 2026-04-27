<?php
declare(strict_types=1);

namespace App\Application\Actions\Campaign;

use App\Application\Actions\Action;
use App\Domain\Affiliate\AffiliateRepository;
use App\Domain\Brand\BrandRepository;
use App\Domain\Campaign\DealRepository;
use App\Domain\Influencer\InfluencerRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class ListMyDealsAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private DealRepository $deals,
        private BrandRepository $brands,
        private InfluencerRepository $influencers,
        private AffiliateRepository $affiliates,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        /** @var User $user */
        $user = $this->request->getAttribute('user');
        $dealsList = [];

        switch ($user->getRole()) {
            case 'brand':
                $brand = $this->brands->findByUserId($user->getId());
                if ($brand) {
                    $dealsList = $this->deals->findByBrandId($brand->getId());
                }
                break;

            case 'influencer':
                $influencer = $this->influencers->findByUserId($user->getId());
                if ($influencer) {
                    $dealsList = $this->deals->findByInfluencerId($influencer->getId());
                }
                break;

            case 'affiliate':
                $affiliate = $this->affiliates->findByUserId($user->getId());
                if ($affiliate) {
                    $dealsList = $this->deals->findByAffiliateId($affiliate->getId());
                }
                break;
        }

        return $this->respondWithData(['deals' => $dealsList]);
    }
}
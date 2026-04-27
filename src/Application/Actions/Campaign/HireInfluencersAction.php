<?php
declare(strict_types=1);

namespace App\Application\Actions\Campaign;

use App\Application\Actions\Action;
use App\Application\Services\PricingService;
use App\Application\Validation\RequestValidator;
use App\Domain\Affiliate\AffiliateRepository;
use App\Domain\Brand\BrandRepository;
use App\Domain\Campaign\CampaignRepository;
use App\Domain\Campaign\DealRepository;
use App\Domain\Influencer\InfluencerRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class HireInfluencersAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private BrandRepository $brands,
        private CampaignRepository $campaigns,
        private DealRepository $deals,
        private InfluencerRepository $influencers,
        private AffiliateRepository $affiliates,
        private PricingService $pricing,
        private RequestValidator $validator,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        /** @var User $user */
        $user = $this->request->getAttribute('user');
        $brand = $this->brands->findByUserId($user->getId());

        if (!$brand) {
            throw new HttpNotFoundException($this->request, 'Brand profile not found');
        }

        $campaignId = (int)$this->resolveArg('id');
        if (!$this->campaigns->belongsToBrand($campaignId, $brand->getId())) {
            throw new HttpBadRequestException($this->request, 'Campaign not found');
        }

        $data = $this->validator->validate(
            (array)$this->request->getParsedBody(),
            [
                'hires' => v::notEmpty()->arrayType(),
            ]
        );

        $createdDeals = [];

        foreach ($data['hires'] as $index => $hire) {
            // Validate each hire entry
            if (empty($hire['influencer_id']) || empty($hire['platform']) || empty($hire['content_type'])) {
                throw new HttpBadRequestException(
                    $this->request,
                    "Hire entry #{$index}: influencer_id, platform, and content_type are required"
                );
            }

            $influencer = $this->influencers->findById((int)$hire['influencer_id']);
            if (!$influencer) {
                throw new HttpBadRequestException($this->request, "Hire entry #{$index}: influencer not found");
            }

            // Determine base cost based on content type
            $baseCost = match ($hire['content_type']) {
                'video', 'reel' => $influencer->getBaseCostPerVideo(),
                default         => $influencer->getBaseCostPerPost(),
            };

            // Get affiliate commission if influencer has one
            $affiliateId = $influencer->getAffiliateId();
            $affiliatePercent = 0.0;
            if ($affiliateId) {
                $affiliate = $this->affiliates->findById($affiliateId);
                if ($affiliate) {
                    $affiliatePercent = $affiliate->getDefaultCommissionPercent();
                }
            }

            $quantity = max(1, (int)($hire['quantity'] ?? 1));

            // Calculate frozen pricing
            $pricing = $this->pricing->calculate($baseCost, $affiliatePercent, $quantity);

            $deal = $this->deals->create([
                'campaign_id'   => $campaignId,
                'influencer_id' => $influencer->getId(),
                'affiliate_id'  => $affiliateId,
                'platform'      => $hire['platform'],
                'content_type'  => $hire['content_type'],
                'quantity'      => $quantity,
                'deadline'      => $hire['deadline'] ?? null,
                ...$pricing,
            ]);

            $createdDeals[] = $deal;
        }

        $this->logger->info("Brand {$brand->getName()} hired " . count($createdDeals) . " influencers for campaign #{$campaignId}");

        return $this->respondWithData([
            'message' => count($createdDeals) . ' deal(s) created',
            'deals'   => $createdDeals,
        ], 201);
    }
}
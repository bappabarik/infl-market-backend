<?php
declare(strict_types=1);

namespace App\Application\Actions\Campaign;

use App\Application\Actions\Action;
use App\Domain\Brand\BrandRepository;
use App\Domain\Campaign\CampaignRepository;
use App\Domain\Campaign\DealRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

class ViewCampaignAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private BrandRepository $brands,
        private CampaignRepository $campaigns,
        private DealRepository $deals,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        /** @var User $user */
        $user = $this->request->getAttribute('user');
        $campaignId = (int)$this->resolveArg('id');
        $campaign = $this->campaigns->findById($campaignId);

        if (!$campaign) {
            throw new HttpNotFoundException($this->request, 'Campaign not found');
        }

        // Only the owning brand or admin can view
        if ($user->getRole() !== 'admin') {
            $brand = $this->brands->findByUserId($user->getId());
            if (!$brand || $campaign->getBrandId() !== $brand->getId()) {
                throw new HttpForbiddenException($this->request, 'Access denied');
            }
        }

        $deals = $this->deals->findByCampaignId($campaignId);

        return $this->respondWithData([
            'campaign' => $campaign->jsonSerialize(),
            'deals'    => $deals,
        ]);
    }
}
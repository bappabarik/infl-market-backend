<?php
declare(strict_types=1);

namespace App\Application\Actions\Campaign;

use App\Application\Actions\Action;
use App\Domain\Brand\BrandRepository;
use App\Domain\Campaign\CampaignRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class DeleteCampaignAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private BrandRepository $brands,
        private CampaignRepository $campaigns,
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

        $this->campaigns->delete($campaignId);

        return $this->respondWithData(['message' => 'Campaign cancelled']);
    }
}
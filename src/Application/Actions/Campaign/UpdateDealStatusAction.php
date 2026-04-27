<?php
declare(strict_types=1);

namespace App\Application\Actions\Campaign;

use App\Application\Actions\Action;
use App\Domain\Brand\BrandRepository;
use App\Domain\Campaign\DealRepository;
use App\Domain\Influencer\InfluencerRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

class UpdateDealStatusAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private DealRepository $deals,
        private InfluencerRepository $influencers,
        private BrandRepository $brands,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        /** @var User $user */
        $user = $this->request->getAttribute('user');
        $dealId = (int)$this->resolveArg('id');
        $action = $this->resolveArg('action');

        $deal = $this->deals->findById($dealId);
        if (!$deal) {
            throw new HttpNotFoundException($this->request, 'Deal not found');
        }

        // Validate the action and check permissions
        switch ($action) {
            case 'accept':
                $this->requireInfluencerOwnership($user, (int)$deal['influencer_id']);
                $this->requireDealStatus($deal, 'pending');
                $newStatus = 'accepted';
                break;

            case 'reject':
                $this->requireInfluencerOwnership($user, (int)$deal['influencer_id']);
                $this->requireDealStatus($deal, 'pending');
                $newStatus = 'rejected';
                break;

            case 'approve':
                $this->requireBrandOwnership($user, (int)$deal['brand_id']);
                $this->requireDealStatus($deal, 'submitted');
                $newStatus = 'approved';
                break;

            case 'complete':
                $this->requireBrandOwnership($user, (int)$deal['brand_id']);
                $this->requireDealStatus($deal, 'approved');
                $newStatus = 'completed';
                break;

            default:
                throw new HttpBadRequestException($this->request, 'Invalid action. Use: accept, reject, approve, complete');
        }

        $updated = $this->deals->updateStatus($dealId, $newStatus);

        return $this->respondWithData($updated);
    }

    private function requireInfluencerOwnership(User $user, int $influencerId): void
    {
        if ($user->getRole() !== 'influencer') {
            throw new HttpForbiddenException($this->request, 'Only influencers can perform this action');
        }
        $influencer = $this->influencers->findByUserId($user->getId());
        if (!$influencer || $influencer->getId() !== $influencerId) {
            throw new HttpForbiddenException($this->request, 'This deal is not assigned to you');
        }
    }

    private function requireBrandOwnership(User $user, int $brandId): void
    {
        if ($user->getRole() !== 'brand') {
            throw new HttpForbiddenException($this->request, 'Only brands can perform this action');
        }
        $brand = $this->brands->findByUserId($user->getId());
        if (!$brand || $brand->getId() !== $brandId) {
            throw new HttpForbiddenException($this->request, 'This deal does not belong to your campaign');
        }
    }

    private function requireDealStatus(array $deal, string $required): void
    {
        if ($deal['status'] !== $required) {
            throw new HttpBadRequestException(
                $this->request,
                "Deal must be in '{$required}' status. Current status: '{$deal['status']}'"
            );
        }
    }
}
<?php
declare(strict_types=1);

namespace App\Application\Actions\Campaign;

use App\Application\Actions\Action;
use App\Application\Validation\RequestValidator;
use App\Domain\Brand\BrandRepository;
use App\Domain\Campaign\CampaignRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class UpdateCampaignAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private BrandRepository $brands,
        private CampaignRepository $campaigns,
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
                'title'      => v::optional(v::stringType()->length(2, 191)),
                'brief'      => v::optional(v::stringType()->length(1, 5000)),
                'budget'     => v::optional(v::numericVal()->min(0)),
                'start_date' => v::optional(v::date('Y-m-d')),
                'end_date'   => v::optional(v::date('Y-m-d')),
                'status'     => v::optional(v::in(['draft', 'active', 'completed', 'cancelled'])),
            ]
        );

        $campaign = $this->campaigns->update($campaignId, $data);

        return $this->respondWithData($campaign->jsonSerialize());
    }
}
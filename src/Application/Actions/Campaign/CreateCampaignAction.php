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
use Slim\Exception\HttpNotFoundException;

class CreateCampaignAction extends Action
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

        $data = $this->validator->validate(
            (array)$this->request->getParsedBody(),
            [
                'title'      => v::notEmpty()->stringType()->length(2, 191),
                'product_id' => v::optional(v::intType()->positive()),
                'brief'      => v::optional(v::stringType()->length(1, 5000)),
                'budget'     => v::optional(v::numericVal()->min(0)),
                'start_date' => v::optional(v::date('Y-m-d')),
                'end_date'   => v::optional(v::date('Y-m-d')),
            ]
        );

        $data['brand_id'] = $brand->getId();
        $campaign = $this->campaigns->create($data);

        return $this->respondWithData($campaign->jsonSerialize(), 201);
    }
}
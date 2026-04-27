<?php
declare(strict_types=1);

namespace App\Application\Actions\Affiliate;

use App\Application\Actions\Action;
use App\Application\Validation\RequestValidator;
use App\Domain\Affiliate\AffiliateRepository;
use App\Domain\Influencer\InfluencerRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class UpdateAffiliateInfluencerAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private AffiliateRepository $affiliates,
        private InfluencerRepository $influencers,
        private RequestValidator $validator,
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

        $influencerId = (int)$this->resolveArg('id');
        $influencer = $this->influencers->findById($influencerId);

        if (!$influencer || $influencer->getAffiliateId() !== $affiliate->getId()) {
            throw new HttpBadRequestException($this->request, 'Influencer not found under your account');
        }

        $data = $this->validator->validate(
            (array)$this->request->getParsedBody(),
            [
                'base_cost_per_post'  => v::optional(v::numericVal()->min(0)),
                'base_cost_per_video' => v::optional(v::numericVal()->min(0)),
                'display_name'        => v::optional(v::stringType()->length(2, 150)),
                'bio'                 => v::optional(v::stringType()->length(1, 5000)),
                'category_id'         => v::optional(v::intType()->positive()),
            ]
        );

        $updated = $this->influencers->update($influencerId, $data);

        return $this->respondWithData($updated->jsonSerialize());
    }
}
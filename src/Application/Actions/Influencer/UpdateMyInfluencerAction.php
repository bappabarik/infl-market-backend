<?php
declare(strict_types=1);

namespace App\Application\Actions\Influencer;

use App\Application\Actions\Action;
use App\Application\Validation\RequestValidator;
use App\Domain\Influencer\InfluencerRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Slim\Exception\HttpNotFoundException;

class UpdateMyInfluencerAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private InfluencerRepository $influencers,
        private RequestValidator $validator,
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        /** @var User $user */
        $user = $this->request->getAttribute('user');
        $influencer = $this->influencers->findByUserId($user->getId());

        if (!$influencer) {
            throw new HttpNotFoundException($this->request, 'Influencer profile not found');
        }

        $data = $this->validator->validate(
            (array)$this->request->getParsedBody(),
            [
                'display_name'       => v::optional(v::stringType()->length(2, 150)),
                'bio'                => v::optional(v::stringType()->length(1, 5000)),
                'category_id'        => v::optional(v::intType()->positive()),
                'location'           => v::optional(v::stringType()->length(1, 150)),
                'languages'          => v::optional(v::arrayType()),
                'base_cost_per_post' => v::optional(v::numericVal()->min(0)),
                'base_cost_per_video'=> v::optional(v::numericVal()->min(0)),
                'currency'           => v::optional(v::stringType()->length(3, 3)),
            ]
        );

        $updated = $this->influencers->update($influencer->getId(), $data);

        return $this->respondWithData($updated->jsonSerialize());
    }
}
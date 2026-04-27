<?php
declare(strict_types=1);

namespace App\Application\Actions\Affiliate;

use App\Application\Actions\Action;
use App\Application\Validation\RequestValidator;
use App\Domain\Affiliate\AffiliateRepository;
use App\Domain\Influencer\InfluencerRepository;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class CreateAffiliateInfluencerAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private AffiliateRepository $affiliates,
        private UserRepository $users,
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

        $data = $this->validator->validate(
            (array)$this->request->getParsedBody(),
            [
                'email'              => v::notEmpty()->email()->length(null, 191),
                'full_name'          => v::notEmpty()->stringType()->length(2, 150),
                'password'           => v::notEmpty()->stringType()->length(8, 100),
                'base_cost_per_post' => v::optional(v::numericVal()->min(0)),
                'base_cost_per_video'=> v::optional(v::numericVal()->min(0)),
            ]
        );

        if ($this->users->emailExists($data['email'])) {
            throw new HttpBadRequestException($this->request, 'Email already registered');
        }

        // Create user account
        $newUser = $this->users->create(
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['full_name'],
            'influencer',
        );

        // Create influencer profile linked to this affiliate
        $influencer = $this->influencers->create(
            $newUser->getId(),
            $data['full_name'],
            $affiliate->getId(),
        );

        // Update costs if provided
        $updateData = [];
        if (isset($data['base_cost_per_post'])) {
            $updateData['base_cost_per_post'] = $data['base_cost_per_post'];
        }
        if (isset($data['base_cost_per_video'])) {
            $updateData['base_cost_per_video'] = $data['base_cost_per_video'];
        }
        if (!empty($updateData)) {
            $influencer = $this->influencers->update($influencer->getId(), $updateData);
        }

        return $this->respondWithData([
            'user'       => $newUser->jsonSerialize(),
            'influencer' => $influencer->jsonSerialize(),
        ], 201);
    }
}
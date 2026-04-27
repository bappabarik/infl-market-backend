<?php
declare(strict_types=1);

namespace App\Application\Actions\Influencer;

use App\Application\Actions\Action;
use App\Application\Validation\RequestValidator;
use App\Domain\Influencer\InfluencerRepository;
use App\Domain\Influencer\SocialAccountRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;
use Slim\Exception\HttpNotFoundException;

class AddSocialAccountAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private InfluencerRepository $influencers,
        private SocialAccountRepository $socialAccounts,
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
                'platform'        => v::notEmpty()->in(['instagram', 'facebook', 'youtube', 'twitter', 'tiktok']),
                'handle'          => v::notEmpty()->stringType()->length(1, 150),
                'profile_url'     => v::optional(v::url()),
                'followers_count' => v::optional(v::intType()->min(0)),
                'is_primary'      => v::optional(v::boolType()),
            ]
        );

        $account = $this->socialAccounts->create($influencer->getId(), $data);

        return $this->respondWithData($account, 201);
    }
}
<?php
declare(strict_types=1);

namespace App\Application\Actions\Influencer;

use App\Application\Actions\Action;
use App\Domain\Influencer\InfluencerRepository;
use App\Domain\Influencer\SocialAccountRepository;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class DeleteSocialAccountAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private InfluencerRepository $influencers,
        private SocialAccountRepository $socialAccounts,
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

        $accountId = (int)$this->resolveArg('id');

        if (!$this->socialAccounts->belongsToInfluencer($accountId, $influencer->getId())) {
            throw new HttpBadRequestException($this->request, 'Social account not found');
        }

        $this->socialAccounts->delete($accountId);

        return $this->respondWithData(['message' => 'Social account removed']);
    }
}
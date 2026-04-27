<?php
declare(strict_types=1);

namespace App\Application\Actions\Influencer;

use App\Application\Actions\Action;
use App\Domain\Influencer\InfluencerRepository;
use App\Domain\Influencer\SocialAccountRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

class GetInfluencerPostsAction extends Action
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
        $influencerId = (int)$this->resolveArg('id');
        $influencer = $this->influencers->findById($influencerId);

        if (!$influencer) {
            throw new HttpNotFoundException($this->request, 'Influencer not found');
        }

        $accounts = $this->socialAccounts->findByInfluencerId($influencerId);
        $allPosts = [];

        foreach ($accounts as $account) {
            $posts = $this->socialAccounts->getRecentPosts((int)$account['id'], 10);
            $allPosts[] = [
                'platform' => $account['platform'],
                'handle'   => $account['handle'],
                'posts'    => $posts,
            ];
        }

        return $this->respondWithData([
            'influencer_id' => $influencerId,
            'platforms'     => $allPosts,
        ]);
    }
}
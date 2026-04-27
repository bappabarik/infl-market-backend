<?php
declare(strict_types=1);

namespace App\Application\Actions\Influencer;

use App\Application\Actions\Action;
use App\Domain\Influencer\InfluencerRepository;
use App\Domain\Influencer\SocialAccountRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

class ViewInfluencerAction extends Action
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
        $id = (int)$this->resolveArg('id');
        $influencer = $this->influencers->findById($id);

        if (!$influencer) {
            throw new HttpNotFoundException($this->request, 'Influencer not found');
        }

        $accounts = $this->socialAccounts->findByInfluencerId($id);

        return $this->respondWithData([
            'influencer'      => $influencer->jsonSerialize(),
            'social_accounts' => $accounts,
        ]);
    }
}